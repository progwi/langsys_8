<?php
namespace App\Models;

use App\Models\User;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="person")
 */
class Person implements JsonSerializable
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	private $id;

	/**
	 * @ORM\Column(type="string")
	 */
	private $firstName;

	/**
	 * @ORM\Column(type="string")
	 */
	private $lastName;

	/**
	 * @ORM\Column(type="float")
	 */
	private $height;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $birthDate;

	/**
	 * @ORM\OneToOne(targetEntity="User", inversedBy="person", cascade={"remove"})
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	private $user;

	public function __construct(
		$data = [
			"firstName"	=> "",
			"lastName"	=> "",
			"height"	=> 0,
			"birthDate"	=> ""
		]
	) {
		$this->setFirstName($data["firstName"]);
		$this->setLastName($data["lastName"]);
		$this->setHeight($data["height"] ?? 0);
		$this->setBirthDate($data["birthDate"] ?? "");
	}

	public function getId()
	{
		return $this->id;
	}

	public function getFirstName()
	{
		return $this->firstName;
	}

	public function getLastName()
	{
		return $this->lastName;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function setFirstName($firstName)
	{
		if (isset($firstName) && is_string($firstName) && strlen($firstName) > 0) {
			$this->firstName = $firstName;
		}
	}

	public function setLastName($lastName)
	{
		if (isset($lastName) && is_string($lastName) && strlen($lastName) > 0) {
			$this->lastName = $lastName;
		}
	}

	public function setHeight($height)
	{
		if (isset($height)) {
			$this->height = $height;
		}
	}

	public function setBirthDate($birthDate)
	{
		if (isset($birthDate)) {
			$this->birthDate = new DateTime($birthDate);
		}
	}

	public function getFullName()
	{
		return strtoupper($this->lastName) . ", " . $this->firstName;
	}

	public function setUser(User $user)
	{
		$this->user = $user;
	}

	public function jsonSerialize()
	{
		return [
			"id"		=> $this->id,
			"firstName"	=> $this->firstName,
			"lastName"	=> $this->lastName,
			"height"	=> $this->height,
			"birthDate"	=> $this->birthDate->format("Y-m-d")
		];
	}

	/*
	public function __toString()
	{
		return $this->firstName . ' ' . $this->lastName . ' who is ' . $this->user->maxRole();
	}
	*/
}
