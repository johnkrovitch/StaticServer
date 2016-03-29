<?php

namespace AppBundle\Form\Handler;

use AppBundle\Assets\AssetsManager;
use Exception;
use SplFileInfo;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

    /**
     * @var string
     */
    protected $cacheDirectory;

    /**
     * FilePostHandler constructor.
     *
     * @param array $allowedApplications
     * @param AssetsManager $assetsManager
     * @param string $cacheDirectory
     */
    public function __construct(
        $allowedApplications = [],
        AssetsManager $assetsManager,
        $cacheDirectory
    ) {
        $this->allowedApplications = new ParameterBag($allowedApplications);
        $this->assetsManager = $assetsManager;
        $this->cacheDirectory = $cacheDirectory;
    }

    /**
     * @param FormInterface $form
     * @return string
     * @throws Exception
     */
    public function handle(FormInterface $form)
    {
        $data = $form->getData();

        if (!$this->allowedApplications->has($data['application'])) {
            throw new NotFoundHttpException('Application not found' . print_r($this->allowedApplications->all(), true));
        }
        if ($this->allowedApplications->get($data['application']) != $data['password']) {
            throw new AccessDeniedException('Access denied for the pair application/password');
        }
        if (empty($data['file'])) {
            throw new Exception('Trying to upload empty file');
        }

        $file = $data['file'];

        if (!($file instanceof UploadedFile)) {
            throw new Exception('Invalid file type. Expected ' . UploadedFile::class. ', got ' . get_class($file));
        }
        // move uploaded file
        $file->move($this->cacheDirectory . '/jk-static-server/', $file->getClientOriginalName());

        // create new file
        $downloadedFile = new SplFileInfo($this->cacheDirectory . '/jk-static-server/' . $file->getClientOriginalName());

        // add it to the manager
        $fileUrl = $this
            ->assetsManager
            ->add($data['application'], $downloadedFile);

        return $fileUrl;
    }
}
