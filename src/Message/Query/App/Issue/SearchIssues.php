<?php

namespace App\Message\Query\App\Issue;

use App\Entity\User;
use App\Model\Filter\IssueFilter;
use App\Model\SortParams;

class SearchIssues
{
    public const int MAX_ISSUES_RESULTS = 10;

    public SortParams $sort;

    public function __construct(
        string $sort,
        public int $page = 1,
        public ?User $user = null,
        public bool $onlyUserAssigned = false,
        public ?IssueFilter $filter = null,
    ) {
        $this->sort = SortParams::createSort($sort);
    }
}
