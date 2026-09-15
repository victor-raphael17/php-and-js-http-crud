<?php

function emptyData(): array
{
    return ['users' => [], 'nextId' => 1];
}

function loadData(string $dataFile): array
{
    if (!is_file($dataFile)) {
        return emptyData();
    }

    $content = file_get_contents($dataFile);

    if ($content === false) {
        return emptyData();
    }

    $data = json_decode($content, true);

    if (!is_array($data) || !isset($data['users'], $data['nextId'])) {
        return emptyData();
    }

    return $data;
}

function saveData(string $dataFile, array $data): void
{
    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

/**
 * Serializa um ciclo ler → alterar → gravar.
 *
 * Sem isso, duas requisições simultâneas leem o mesmo estado e a segunda
 * gravação apaga a primeira. O flock faz a segunda requisição esperar.
 */
function withDataLock(string $dataFile, callable $operation): mixed
{
    // 'c' cria o arquivo se não existir e não apaga o conteúdo.
    $lock = fopen($dataFile, 'c');

    if ($lock === false) {
        throw new RuntimeException('Could not open the data file');
    }

    try {
        if (!flock($lock, LOCK_EX)) {
            throw new RuntimeException('Could not lock the data file');
        }

        return $operation();
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

function findUserById(string $dataFile, int $id): ?array
{
    $data = loadData($dataFile);

    foreach ($data['users'] as $user) {
        if ($user['id'] === $id) {
            return $user;
        }
    }

    return null;
}

function insertUser(string $dataFile, array $user): array
{
    return withDataLock($dataFile, function () use ($dataFile, $user): array {
        $data = loadData($dataFile);

        $id = $data['nextId'];
        $data['nextId'] = $id + 1;
        $user['id'] = $id;
        $data['users'][] = $user;

        saveData($dataFile, $data);

        return $user;
    });
}

function updateUser(string $dataFile, int $id, array $fields): ?array
{
    return withDataLock($dataFile, function () use ($dataFile, $id, $fields): ?array {
        $data = loadData($dataFile);
        $users = $data['users'];

        for ($i = 0; $i < count($users); $i++) {
            if ($users[$i]['id'] === $id) {
                $data['users'][$i] = array_merge($users[$i], $fields);
                saveData($dataFile, $data);

                return $data['users'][$i];
            }
        }

        return null;
    });
}

function deleteUser(string $dataFile, int $id): ?array
{
    return withDataLock($dataFile, function () use ($dataFile, $id): ?array {
        $data = loadData($dataFile);
        $users = $data['users'];

        for ($i = 0; $i < count($users); $i++) {
            if ($users[$i]['id'] === $id) {
                $user = $users[$i];
                array_splice($users, $i, 1);
                $data['users'] = $users;
                saveData($dataFile, $data);

                return $user;
            }
        }

        return null;
    });
}
