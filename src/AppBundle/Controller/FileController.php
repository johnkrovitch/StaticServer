<?php

namespace AppBundle\Controller;

use AppBundle\Form\Type\FilePostType;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Main application controller. Server, upload and delete (in progress...) files
 */
class FileController extends Controller
{
    /**
     * Post a file for a given application
     *
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function postAction(Request $request)
    {
        // posted application and password should be valid
        $form = $this->createForm(FilePostType::class);
        $form->handleRequest($request);

        if ($form->isValid()) {
            // upload and move file into the resources directory, return the file name
            $filename = $this
                ->get('app.file.post_handler')
                ->handle($form);

            // generate file url from its name and the configured host
            $host = $this->getParameter('server_host');

            if (substr($host, -1, 1)) {
                $host = substr($host, 0, strlen($host) - 1);
            }
            $url = $host . $this
                ->generateUrl('app.file.serve', [
                    'application' => $form->getData()['application'],
                    'filename' => $filename
                ]);
        } else {
            throw new Exception('Invalid data : ' . $form->getErrors());
        }
        return new Response($url);
    }

    /**
     * Serve a file for a given application
     *
     * @param $application
     * @param $filename
     * @return BinaryFileResponse
     */
    public function serveAction($application, $filename)
    {
        $assetsManager = $this->get('app.assets.manager');

        if (!$assetsManager->exists($application, $filename)) {
            throw $this->createNotFoundException(sprintf('File %s not found for the given application %s',
                $application, $filename));
        }
        return new BinaryFileResponse($assetsManager->path($application, $filename));
    }
}
