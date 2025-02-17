<?php
namespace App\Core\Utils;

class Pagination{

    private $results;
    private $count;
    private $nbrPages;
    private $currentPage;
    private $firstPage;
    private $lastPage;
    private $offset;
    private $limit;
    private $maxPageShow;
    public function __construct(int $count,array $results, int $currentPage = 1, int $limit = 10){
        $this->count = $count;
        $this->results = $results;
        $this->maxPageShow = 5;
        $this->limit = $limit;
        $this->nbrPages = ceil($this->count / $this->limit);
        $this->currentPage = $currentPage;
        $this->firstPage = 1;
        $this->lastPage = $this->nbrPages;
        $this->offset = (($this->currentPage - 1) * $this->limit);
    }

    public function getPagination()
    {
        $diff = floor($this->maxPageShow / 2);
        $firstBetween = $this->currentPage - $diff;
        $lastBetween = $this->currentPage + $diff;

        if($firstBetween <= 0){
            $firstBetween = 1;
        }

        if($lastBetween > $this->lastPage){
            $lastBetween = $this->lastPage;
        }

        return [
            'start' => $this->firstPage,
            'end' => $this->lastPage,
            'between' => range($firstBetween, $lastBetween),
            'nbrPages' => $this->nbrPages,
            'currentPage' => $this->currentPage,
        ];
    }

    public function getData(bool $withPagination = true): array
    {
        return [
            'total' => $this->count,
            'limit' => $this->limit,
            'page' => $this->currentPage,
            'results' => $this->results,
            'pagination' => $withPagination ? $this->getPagination() : null,
        ];
    }

}