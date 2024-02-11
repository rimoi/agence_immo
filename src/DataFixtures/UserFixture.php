<?php

namespace App\DataFixtures;

use App\Entity\ApiToken;
use App\Entity\User;
use App\Service\UploaderHelper;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixture extends BaseFixture
{

    private static $avatars = [
        'avatar1.jpg',
        'avatar2.jpg',
        'avatar3.jpg',
    ];

    private $passwordEncoder;
    /**
     * @var UploaderHelper
     */
    private $uploaderHelper;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        UploaderHelper $uploaderHelper
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->uploaderHelper = $uploaderHelper;
    }

    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(10, 'main_users', function($i) use ($manager) {
            $user = new User();
            $user->setEmail(sprintf('spacebar%d@example.com', $i));
            $user->setFirstName($this->faker->firstName);
            $user->setLastName($this->faker->lastName);
            $user->setPhone('0606060606');

            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                'engage'
            ));

            return $user;
        });

        $this->createMany(3, 'admin_users', function($i) {
            $user = new User();
            $user->setEmail(sprintf('admin%d@thespacebar.com', $i));
            $user->setFirstName($this->faker->firstName);
            $user->setLastName($this->faker->lastName);
            $user->setRoles(['ROLE_ADMIN']);
            $user->setPhone($this->faker->phoneNumber);

            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                'engage'
            ));

            return $user;
        });

        $manager->flush();
    }

    private function fakeUploadImage(): string
    {
        $randomImage = $this->faker->randomElement(self::$avatars);
        $fs = new Filesystem();

        $targetPath = sys_get_temp_dir().'/'.$randomImage;
        $fs->copy(__DIR__.'/avatar/'.$randomImage, $targetPath, true);

        return $this->uploaderHelper->uploadUserAvatar(new File($targetPath), null);
    }
}
