<?php

declare(strict_types=1);

namespace App\Route\Controllers\Users;

use App\Models\User;
use App\Repository\Exceptions\EntityNotFoundException;
use App\Repository\RepositoryInterface;
use App\Repository\UserRepository;
use App\Route\Attributes\{DomainKeyAttribute, MethodRouteAttribute};
use App\Route\Controllers\RouteControllerInterface;
use App\Route\Exceptions\StatusErrorException;
use App\Validation\UserValidator;

#[DomainKeyAttribute('/users')]
class UserController implements RouteControllerInterface
{
    protected RepositoryInterface $userRepository;
    protected UserValidator $userValidator;

    public function __construct(
    )
    {
        $this->userRepository = new UserRepository();
        $this->userValidator = new UserValidator();
    }

    #[MethodRouteAttribute('GET', '/users')]
    public function index(): void
    {
        $users = $this->userRepository->getAll();

        echo json_encode($users);
    }

    /**
     * @throws StatusErrorException
     */
    #[MethodRouteAttribute('GET', '/users/{id}')]
    public function get(int $id): void
    {
        try {
            $user = $this->userRepository->getById($id);
        } catch (EntityNotFoundException $exception) {

            throw new StatusErrorException($exception->getMessage(), 404);
        }

        echo json_encode($user);
    }

    #[MethodRouteAttribute('POST', '/users')]
    public function save(User $user): void
    {
        $this->userValidator->setUser($user);
        $shouldSave = $this->userValidator->validate();

        echo $shouldSave;
    }
}
