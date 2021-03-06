<?php

declare(strict_types=1);

namespace App\Service\Task;

final class Find extends Base
{  
    public function getAllByUser(int $userId): array
    {
        return $this->taskRepository->getTasksByUserId($userId);
    }

    public function getAll(): array
    {
        return $this->taskRepository->getTasks();
    }

    public function getTasksByPage(
        int $userId,
        int $page,
        int $perPage,
        ?string $name,
        ?string $description,
        ?string $status
    ): array {
        if ($page < 1) {
            $page = 1;
        }
        if ($perPage < 1) {
            $perPage = self::DEFAULT_PER_PAGE_PAGINATION;
        }

        return $this->taskRepository->getTasksByPage(
            $userId,
            $page,
            $perPage,
            $name,
            $description,
            $status
        );
    }

    public function getOne(int $taskId, int $userId): object
    {
        if (self::isRedisEnabled() === true) {
            $task = $this->getTaskFromCache($taskId, $userId);
        } else {
            $task = $this->getTaskFromDb($taskId, $userId)->toJson();
        }

        return $task;
    }

    public function search(string $tasksName, int $userId, ?string $status): array
    {
        if ($status !== null) {
            $status = (int) $status;
        }

        return $this->taskRepository->searchTasks($tasksName, $userId, $status);
    }
}
