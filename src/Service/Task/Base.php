<?php

declare(strict_types=1);

namespace App\Service\Task;

use App\Exception\TaskException;
use App\Repository\TaskRepository;
use App\Service\BaseService;
use App\Service\RedisService;
use App\Service\LoggerService;
use Respect\Validation\Validator as v;

abstract class Base extends BaseService
{
    private const REDIS_KEY = 'task:%s:user:%s';
    protected TaskRepository $taskRepository;
    protected RedisService $redisService;
    protected LoggerService $loggerService;

    public function __construct(
        TaskRepository $taskRepository,
        RedisService $redisService,
        LoggerService $loggerService
    ) {
        $this->taskRepository = $taskRepository;
        $this->redisService = $redisService;
        $this->loggerService = $loggerService;
    }

    protected static function validateTaskName(string $name): string
    {
        if (!v::length(1, 100)->validate($name)) {
            throw new TaskException('Invalid name.', 400);
        }

        return $name;
    }

    protected static function validateTaskStatus(int $status): int
    {
        if (!v::numeric()->between(0, 1)->validate($status)) {
            throw new TaskException('Invalid status', 400);
        }

        return $status;
    }

    protected function getTaskFromCache(int $taskId, int $userId): object
    {
        $redisKey = sprintf(self::REDIS_KEY, $taskId, $userId);
        $key = $this->redisService->generateKey($redisKey);
        if ($this->redisService->exists($key)) {
            $task = $this->redisService->get($key);
        } else {
            $task = $this->getTaskFromDb($taskId, $userId)->toJson();
            $this->redisService->setex($key, $task);
        }

        return $task;
    }

    protected function getTaskFromDb(int $taskId, int $userId): \App\Entity\Task
    {
        return $this->taskRepository->checkAndGetTask($taskId, $userId);
    }

    protected function saveInCache(int $taskId, int $userId, object $task): void
    {
        $redisKey = sprintf(self::REDIS_KEY, $taskId, $userId);
        $key = $this->redisService->generateKey($redisKey);
        $this->redisService->setex($key, $task);
    }

    protected function deleteFromCache(int $taskId, int $userId): void
    {
        $redisKey = sprintf(self::REDIS_KEY, $taskId, $userId);
        $key = $this->redisService->generateKey($redisKey);
        $this->redisService->del([$key]);
    }
}
