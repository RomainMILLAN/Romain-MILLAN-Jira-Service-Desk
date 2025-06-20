<?php

namespace App\Controller\App\Project\Issue;

use App\Controller\App\Issue\RouteCollection as IssueRouteCollection;
use App\Controller\App\Project\AbstractController;
use App\Controller\Common\CreateControllerTrait;
use App\Entity\Project;
use App\Entity\User;
use App\Form\App\Issue\CreateIssueFormType;
use App\Message\Command\App\Issue\CreateIssue;
use App\Message\Query\App\Issue\GetIssueAssignableUsers;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(
    path: '/project/{key}/issues/create',
    name: RouteCollection::CREATE->value,
    methods: [Request::METHOD_GET, Request::METHOD_POST],
)]
class CreateController extends AbstractController
{
    use CreateControllerTrait;

    public function __invoke(
        Request $request,
        #[MapEntity(mapping: [
            'key' => 'jiraKey',
        ])]
        Project $project,
        #[CurrentUser]
        User $user,
    ): Response {
        $this->setCurrentProject($project);
        $assignableUsers = $this->handle(new GetIssueAssignableUsers($project));

        $form = $this->createForm(
            type: CreateIssueFormType::class,
            data: new CreateIssue(
                project: $project,
                creator: $user,
            ),
            options: [
                'assignees' => $assignableUsers,
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $issue = $this->handle($form->getData());

            if ($issue !== null) {
                $this->addFlash(
                    type: 'success',
                    message: 'flash.created',
                );

                return $this->redirectToRoute(
                    route: IssueRouteCollection::VIEW->prefixed(),
                    parameters: [
                        'keyProject' => $project->jiraKey,
                        'keyIssue' => $issue->key,
                    ]
                );
            }

            $this->addFlash(
                type: 'danger',
                message: 'flash.error',
            );
        }

        return $this->render(
            view: 'app/project/issue/create.html.twig',
            parameters: [
                'project' => $project,
                'form' => $form->createView(),
            ],
        );
    }
}
