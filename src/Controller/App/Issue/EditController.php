<?php

namespace App\Controller\App\Issue;

use App\Controller\Common\EditControllerTrait;
use App\Entity\User;
use App\Form\App\Issue\EditIssueFormType;
use App\Message\Command\App\Issue\EditIssue;
use App\Message\Query\App\Issue\GetFullIssue;
use App\Repository\IssueTypeRepository;
use App\Repository\ProjectRepository;
use JiraCloud\Issue\Issue;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(
    path: '/issue/{key}/edit',
    name: RouteCollection::EDIT->value,
    methods: [Request::METHOD_GET, Request::METHOD_POST],
)]
class EditController extends AbstractController
{
    use EditControllerTrait;

    public function __construct(
        private readonly IssueTypeRepository $issueTypeRepository,
        private readonly ProjectRepository $projectRepository,
    ) {
    }

    public function __invoke(
        string $key,
        Request $request,
        #[CurrentUser]
        User $user,
    ): Response {
        /** @var Issue $issue */
        $issue = $this->handle(
            new GetFullIssue($key),
        );
        $issueTransitions = [];
        foreach ($issue->transitions as $issueTransition) {
            $issueTransitions[$issueTransition->name] = $issueTransition->id;
        }
        $issueTransitionIdCurrentStatusIssue = null;
        foreach ($issue->transitions as $issueTransition) {
            if ($issue->fields->status->id == $issueTransition->to->id) {
                $issueTransitionIdCurrentStatusIssue = $issueTransition->id;
                break;
            }
        }
        $project = $this->projectRepository->findOneBy([
            'jiraKey' => $issue->fields->project->key,
        ]);

        $form = $this->createForm(
            type: EditIssueFormType::class,
            data: new EditIssue(
                project: $project,
                issue: $issue,
                issueType: $this->issueTypeRepository->findOneBy([
                    'jiraId' => $issue->fields->issuetype->id,
                ]),
                transition: $issueTransitionIdCurrentStatusIssue,
            ),
            options: [
                'projectId' => $project?->getId() ?? null,
                'transitions' => $issueTransitions,
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $jiraIssueEdited = $this->handle($form->getData());

            if ($jiraIssueEdited !== null) {
                $this->addFlash(
                    type: 'success',
                    message: 'flash.edited',
                );

                return $this->redirectToRoute(
                    route: RouteCollection::VIEW->prefixed(),
                    parameters: [
                        'key' => $key,
                    ],
                );
            }

            $this->addFlash(
                type: 'danger',
                message: 'flash.error',
            );
        }

        return $this->render(
            view: 'app/issue/edit.html.twig',
            parameters: [
                'key' => $key,
                'issue' => $issue,
                'form' => $form->createView(),
            ],
        );
    }
}
