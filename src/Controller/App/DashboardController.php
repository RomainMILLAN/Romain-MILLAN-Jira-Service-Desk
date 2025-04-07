<?php

namespace App\Controller\App;

use App\Controller\Common\GetControllerTrait;
use App\Entity\User;
use App\Message\Query\App\Issue\SearchIssues;
use App\Model\SearchIssuesResult;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(
    path: '/dashboard',
    name: RouteCollection::DASHBOARD->value,
    methods: [Request::METHOD_GET]
)]
class DashboardController extends AbstractController
{
    use GetControllerTrait;

    public function __invoke(
        #[CurrentUser]
        User $user,
        Request $request,
    ): Response {
        /** @var SearchIssuesResult $searchIssueResult */
        $searchIssueResult = $this->handle(
            new SearchIssues(
                sort: $request->get('_sort', 'id'),
                page: $request->get('page', 1),
                user: $user,
                onlyUserAssigned: true,
            )
        );

        return $this->render(
            view: 'app/dashboard.html.twig',
            parameters: [
                'projects' => $user->getProjects(),
                'searchIssuesResult' => $searchIssueResult,
            ]
        );
    }
}
