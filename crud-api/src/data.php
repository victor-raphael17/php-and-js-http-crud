<?php

function loadData(string $dataFile): array
{
    $data = json_decode(file_get_contents($dataFile), true);

    if ($data === null) {
        $data = ['users' => [], 'nextId' => 1];
    }

    return $data;
}

function saveData(string $dataFile, array $data): void
{
    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
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
    $data = loadData($dataFile);

    $id = $data['nextId'];
    $data['nextId'] = $id + 1;
    $user['id'] = $id;
    $data['users'][] = $user;

    saveData($dataFile, $data);

    return $user;
}

function updateUser(string $dataFile, int $id, array $fields): ?array
{
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
}

function deleteUser(string $dataFile, int $id): ?array
{
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
}
