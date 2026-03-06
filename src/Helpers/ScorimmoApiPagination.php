<?php

namespace CLDT\Scorimmo\Helpers;

class ScorimmoApiPagination
{
    protected int $limit;
    protected int $currentPage;
    protected int $totalItems;
    protected int $totalPages;
    protected int $currentPageResults;
    protected ?string $nextPage;
    protected ?string $previousPage;

    public function __construct(array $informations)
    {
        $this->limit = $informations['limit'] ?? 20;
        $this->currentPage = $informations['current_page'] ?? 1;
        $this->totalItems = $informations['total_items'] ?? 0;
        $this->totalPages = $informations['total_pages'] ?? 0;
        $this->currentPageResults = $informations['current_page_results'] ?? 0;
        $this->nextPage = $informations['next_page'] ?? null;
        $this->previousPage = $informations['previous_page'] ?? null;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function getCurrentPageResults(): int
    {
        return $this->currentPageResults;
    }

    public function getNextPage(): ?string
    {
        return $this->nextPage;
    }

    public function getPreviousPage(): ?string
    {
        return $this->previousPage;
    }

    public function toArray(): array
    {
        return [
            'limit' => $this->limit,
            'current_page' => $this->currentPage,
            'total_items' => $this->totalItems,
            'total_pages' => $this->totalPages,
            'current_page_results' => $this->currentPageResults,
            'next_page' => $this->nextPage,
            'previous_page' => $this->previousPage,
        ];
    }
}
