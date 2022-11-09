<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\Person;

class UserService
{
	private $entityManager;

	public function __construct($entityManager)
	{
		$this->entityManager = $entityManager;
	}

	public function create($userData = [
		"name" => "",
		"email" => "",
		"password" => "",
		"roles" => [],
		"person" => [
			"firstName" => "",
			"lastName" => "",
			"height" => 0,
			"birthDate" => ""
		]
	])
	{
		$user = $this->alreadyExists($userData);
		if ($user) {
			return $user;
		}
		$userInfo = [
			'name' => $userData['name'],
			'email' => $userData['email'],
			'password' => $userData['password'] ?? '123456',
		];
		$newUser = new User($userInfo);
		$newPerson = new Person($userData['person']);
		$newUser->setPerson($newPerson);
		$this->entityManager->persist($newUser);
		$this->entityManager->persist($newPerson);
		$this->setRoles($newUser, $userData['roles']);
		$this->entityManager->flush();
		return $newUser;
	}

	public function update($userId, $userData = [
		"name" => "",
		"email" => "",
		"password" => "",
		"roles" => [],
		"person" => [
			"firstName" => null,
			"lastName" => null,
			"height" => null,
			"birthDate" => null
		]
	])
	{
		$response = [];

		$user = $this->getUser($userId);
		if (!$user) {

			$response['success'] = false;
			$response['message'] = 'User ' . $userId . ' not found.';
			return $response;
		}
		$user->setName($userData['name'] ?? null);
		$user->setEmail($userData['email'] ?? null);
		$user->setPassword($userData['password'] ?? null);
		$user->getPerson()->setFirstName($userData['person']['firstName'] ?? null);
		$user->getPerson()->setLastName($userData['person']['lastName'] ?? null);
		$user->getPerson()->setHeight($userData['person']['height'] ?? null);
		$user->getPerson()->setBirthDate($userData['person']['birthDate'] ?? null);
		$this->setRoles($user, $userData['roles'] ?? []);
		$this->entityManager->flush();
		$response['success'] = true;
		$response['message'] = 'User updated successfully';
		$response['data'] = $user;
		return $response;
	}

	public function delete($userId)
	{
		$response = [];

		$user = $this->getUser($userId);
		if (!$user) {
			$response['success'] = false;
			$response['message'] = 'User ' . $userId . ' not found.';
			return $response;
		}

		$this->entityManager->remove($user);
		$this->entityManager->flush();
		$response = [
			'success' => true,
			'message' => 'User deleted successfully',
			'data' => [
				'id' => $userId
			]
		];
		return $response;
	}

	public function alreadyExists($data = [
		"email" => ""
	])
	{
		$existingUser = $this->entityManager->getRepository(User::class)->findOneBy([
			"email" => $data["email"]
		]);
		return $existingUser;
	}

	public function setRoles($user, $roles = [])
	{
		if (count ($roles) == 0) {
			return;
		}
		$user->clearRoles();
		foreach ($roles as $roleId) {
			$roleEntity = $this->entityManager->getRepository(Role::class)->findOneBy(
				['id' => $roleId]
			);
			if ($roleEntity) {
				$user->addRole($roleEntity);
			}
		}
		$this->entityManager->flush();
	}

	public function getUsers()
	{
		$userRepository = $this->entityManager->getRepository(User::class);
		$users = $userRepository->findAll();
		return $users;
	}

	public function getUser($id)
	{
		$userRepository = $this->entityManager->getRepository(User::class);
		$user = $userRepository->find($id);
		return $user;
	}

	public function updateUser($user)
	{
		$this->entityManager->merge($user);
		$this->entityManager->flush();
	}

	public function deleteUser($user)
	{
		$this->entityManager->remove($user);
		$this->entityManager->flush();
	}

	public function jsonQuery()
	{
		$list = $this->query();
		$users = json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
		return $users;
	}

	public function query($pageNumber = 1, $pageSize = 10)
	{
		$offset = ($pageNumber - 1) * $pageSize;
		$dql = "SELECT u FROM App\Models\User u ORDER BY u.name";
		$query = $this->entityManager->createQuery($dql)
			->setFirstResult($offset)
			->setMaxResults($pageSize);
		$users = $query->getResult();
		return $users;
	}
}
