<?php

declare(strict_types=1);

/*
 * This file is part of DivineNii opensource projects.
 *
 * PHP version 7.4 and above required
 *
 * @author    Divine Niiquaye Ibok <divineibok@gmail.com>
 * @copyright 2019 DivineNii (https://divinenii.com/)
 * @license   https://opensource.org/licenses/BSD-3-Clause License
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rade\Database\Doctrine\Form;

use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractExtension;
use Symfony\Component\Form\FormTypeGuesserInterface;

class DoctrineOrmExtension extends AbstractExtension
{
    protected ObjectManager $registry;

    public function __construct(ObjectManager $registry)
    {
        $this->registry = $registry;
    }

    protected function loadTypes(): array
    {
        return [
            new Type\EntityType($this->registry),
        ];
    }

    protected function loadTypeGuesser(): ?FormTypeGuesserInterface
    {
        return new DoctrineOrmTypeGuesser($this->registry);
    }
}
