<?php

declare(strict_types=1);

namespace App\Service\Module;

class Search extends BaseModuleService
{
    public function search(string $modulesName): array
    {
        return $this->moduleRepository->searchModules($modulesName);
    }
}