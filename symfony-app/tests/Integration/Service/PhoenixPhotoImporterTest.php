<?php

declare(strict_types=1);

namespace Tests\Integration\Service;

use App\Connectors\PhoenixClient;
use App\Connectors\PhoenixPhoto;
use App\Entity\PhoenixToken;
use App\Entity\Photo;
use App\Entity\User;
use App\Service\PhoenixPhotoImporter;
use App\Utils\CurrentTimeProvider;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Integration\MockTimeProvider;

final class PhoenixPhotoImporterTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private CurrentTimeProvider $currentTimeProvider;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->truncateTables();

        $this->currentTimeProvider = new MockTimeProvider();
    }

    protected function tearDown(): void
    {
        $this->truncateTables();
        $this->entityManager->close();

        parent::tearDown();
    }

    public function testItImportsPhotosAndSavesTokenForExistingUser(): void
    {
        // given
        $user = $this->createUser('import-user');
        $now = new DateTimeImmutable('2026-04-12 12:34:56');

        self::getContainer()->set(CurrentTimeProvider::class, $this->currentTimeProvider->withTime($now));
        $this->mockPhoenixClient(
            [
                new PhoenixPhoto(11, 'http://localhost:4000/api/photos/11.jpg'),
                new PhoenixPhoto(12, 'http://localhost:4000/api/photos/12.jpg'),
            ]
        );

        $importer = self::getContainer()->get(PhoenixPhotoImporter::class);

        // when
        $importedCount = $importer->importPhotos($user->getId(), 'phoenix-token');

        // then
        self::assertSame(2, $importedCount);

        $this->entityManager->clear();
        $token = $this->entityManager->getRepository(PhoenixToken::class)->findOneBy(['userId' => $user->getId()]);
        self::assertNotNull($token);
        self::assertSame('phoenix-token', $token->getToken());
        self::assertEquals($now, $token->getModifyDate());

        $photos = $this->entityManager->getRepository(Photo::class)->findBy(['user' => $user], ['id' => 'ASC']);
        self::assertCount(2, $photos);
        self::assertSame('http://localhost:4000/api/photos/11.jpg', $photos[0]->getImageUrl());
        self::assertSame('Photo from Phoenix, id: 11', $photos[0]->getDescription());
        self::assertEquals($now, $photos[0]->getTakenAt());
        self::assertSame($user->getId(), $photos[0]->getUser()->getId());
        self::assertSame('http://localhost:4000/api/photos/12.jpg', $photos[1]->getImageUrl());
        self::assertSame('Photo from Phoenix, id: 12', $photos[1]->getDescription());
    }

    public function testItUpdatesExistingPhoenixTokenForUser(): void
    {
        // given
        $user = $this->createUser('token-user');
        $originalDate = new DateTimeImmutable('2026-04-10 08:00:00');
        $updatedDate = new DateTimeImmutable('2026-04-12 09:15:00');

        $existingToken = (new PhoenixToken())
            ->setUserId($user->getId())
            ->setToken('old-token')
            ->setModifyDate($originalDate);

        $this->entityManager->persist($existingToken);
        $this->entityManager->flush();

        self::getContainer()->set(CurrentTimeProvider::class, $this->currentTimeProvider->withTime($updatedDate));
        $this->mockPhoenixClient();

        $importer = self::getContainer()->get(PhoenixPhotoImporter::class);

        // when
        $importedCount = $importer->importPhotos($user->getId(), 'new-token');

        // then
        self::assertSame(0, $importedCount);

        $this->entityManager->clear();
        $tokens = $this->entityManager->getRepository(PhoenixToken::class)->findBy(['userId' => $user->getId()]);
        self::assertCount(1, $tokens);
        self::assertSame('new-token', $tokens[0]->getToken());
        self::assertEquals($updatedDate, $tokens[0]->getModifyDate());
    }

    public function testItThrowsWhenUserDoesNotExist(): void
    {
        // given
        self::getContainer()->set(CurrentTimeProvider::class, $this->currentTimeProvider->withTime(new DateTimeImmutable('2026-04-12 00:00:00')));
        $this->mockPhoenixClient(
            [new PhoenixPhoto(1, 'http://localhost:4000/api/photos/1.jpg')]
        );

        $importer = self::getContainer()->get(PhoenixPhotoImporter::class);

        // when + then
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User with id -1 does not exists!');
        $importer->importPhotos(-1, 'phoenix-token');
    }

    private function mockPhoenixClient(array $photos = []): void
    {
        self::getContainer()->set(PhoenixClient::class, new class($photos) implements PhoenixClient {
            public function __construct(private readonly array $photos)
            {
            }

            public function getPhotos(string $userAccessToken): array
            {
                return $this->photos;
            }
        });
    }

    private function createUser(string $username): User
    {
        $user = (new User())
            ->setUsername($username)
            ->setEmail($username . '@email.com');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function truncateTables(): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement('TRUNCATE TABLE phoenix_token, photos, users RESTART IDENTITY CASCADE');
        $this->entityManager->clear();
    }

}
