<?php

namespace App\Twig;

use App\Constant\UserConstant;
use App\Repository\ConversationRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class StringExtension extends AbstractExtension
{
    private $conversationRepository;

    public function __construct(ConversationRepository $conversationRepository)
    {
        $this->conversationRepository = $conversationRepository;
    }

    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('truncate', [$this, 'displayTruncateFilter']),
            new TwigFilter('translateRole', [$this, 'translateRoleFilter']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getMessageNonLu', [$this, 'getMessageNonLu']),
        ];
    }

    public function displayTruncateFilter(?string $string, int $maxLength): string
    {
        $allowedTag = ['<br/>', '<br />', '<br>', '<p>', '</p>'];
        $replacableTag = '<br />';
        $specialKeyTag = '||';

        $stripTagString = strip_tags($string, implode('', $allowedTag));
        $replaceTagsString = str_replace($allowedTag, $specialKeyTag, $stripTagString);
        $newString = trim($this->safeTruncate($replaceTagsString, $maxLength), $specialKeyTag);

        return str_replace($specialKeyTag, $replacableTag, $newString);
    }

    private function safeTruncate(string $string, int $maxLength): string
    {
        $parts = preg_split('/([\s\n\r]+)/', $string, null, PREG_SPLIT_DELIM_CAPTURE);
        $parts_count = count($parts);

        $length = 0;
        $last_part = 0;

        for (; $last_part < $parts_count; ++$last_part) {
            $length += strlen($parts[$last_part]);

            if ($length > $maxLength) {
                break;
            }
        }

        $newString = implode(array_slice($parts, 0, $last_part));

        if (!$newString && 1 === $parts_count) {
            $newString = substr($string, 0, 20);
        }

        return $newString.(strlen($newString) === strlen($string) ? '' : '...');
    }

    public function getMessageNonLu($user) {
        return $this->conversationRepository->findByParticipationNonLu($user);
    }

    public function translateRoleFilter(string $role): string
    {

        return UserConstant::asStringInverse($role) ?? '';
    }
}
