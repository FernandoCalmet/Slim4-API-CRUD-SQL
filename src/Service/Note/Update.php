<?php

declare(strict_types=1);

namespace App\Service\Note;

final class Update extends Base
{
    public function update(array $input, int $noteId): object
    {
        $note = $this->getOneFromDb($noteId);
        $data = json_decode((string) json_encode($input), false);
        if (isset($data->name)) {
            $note->updateName(self::validateNoteName($data->name));
        }
        if (isset($data->description)) {
            $note->updateDescription($data->description);
        }
        $note->updateUpdatedAt(date('Y-m-d H:i:s'));
        /** @var \App\Entity\Note $response */
        $response = $this->noteRepository->updateNote($note);
        if (self::isRedisEnabled() === true) {
            $this->saveInCache($response->getId(), $response->toJson());
        }
        if (self::isLoggerEnabled() === true) {
            $this->loggerService->setInfo('The note with the ID ' . $response->getId() . ' has updated successfully.');
        }

        return $response->toJson();
    }
}
