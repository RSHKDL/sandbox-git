<?php

namespace App\Controller\TricksController\Interfaces;

use App\Entity\Trick;
use App\Repository\TrickRepository;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

interface ViewTricksControllerInterface
{

    /**
     * ViewTricksControllerInterface constructor.
     * @param TrickRepository $repository
     * @param Environment $environment
     */
    public function __construct(
        TrickRepository $repository,
        Environment $environment
    );

    /**
     * @param Trick $trick
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function view(Trick $trick): Response;
}
