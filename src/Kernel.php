<?php

declare(strict_types=1);

namespace App;

use App\DependencyInjection\Compiler\ResponseTokenPass;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * @return void
     */
    protected function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ResponseTokenPass());
    }
}
