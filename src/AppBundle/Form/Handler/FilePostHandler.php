<?php

namespace AppBundle\Form\Handler;

use AppBundle\Assets\AssetsManager;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class FilePostHandler
{
    /**
     * @var ParameterBag
     */
    protected $allowedApplications;

    /**
     * @var AssetsManager
     */
    protected $assetsManager;

    public function __construct($allowedApplications = [], AssetsManager $assetsManager)
    {
        $this->allowedApplications = new ParameterBag($allowedApplications);
        $this->assetsManager = $assetsManager;
    }

    public function handle(FormInterface $form)
    {
        $data = $form->getData();

        if (!$this->allowedApplications->has($data['application'])) {
            throw new NotFoundHttpException('Application not found');
        }
        if ($this->allowedApplications->get($data['application']) != $data['password']) {
            throw new AccessDeniedException('Access denied for the pair application/password');
        }
        if (empty($data['file'])) {
            throw new Exception('Trying to upload empty file');
        }

        $fileUrl = $this
            ->assetsManager
            ->add($data['application'], $data['file']);

        return $fileUrl;
    }
}
