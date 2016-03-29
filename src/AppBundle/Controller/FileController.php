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
            $slug = $this
                ->get('app.file.post_handler')
                ->handle($form);

            // generate file url from its name and the configured host
            $host = $this->getParameter('server_host');

            if (substr($host, -1, 1)) {
                $host = substr($host, 0, strlen($host) - 1);
            }
            $url = $host;
            $url .= $this->generateUrl('app.file.serve', [
                'application' => $form->getData()['application'],
                'slug' => $slug
            ]);
        } else if (!$form->isSubmitted()) {
            // exception differentiation between "form not submitted" errors and "form invalid" error
            throw new Exception('No form was submitted');
        } else {
            throw new Exception('Invalid data : ' . (string) $form->getErrors());
        }

        // return a response containing the new file url
        return new Response($url);
    }

    /**
     * Serve an existing file
     *
     * @param $application
     * @param $slug
     * @return BinaryFileResponse
     */
    public function serveAction($application, $slug)
    {
        $assetsManager = $this->get('app.assets.manager');

        if (!$assetsManager->exists($application, $slug)) {
            throw $this->createNotFoundException(sprintf('File %s not found for the given application %s',
                $slug, $application));
        }
        return new BinaryFileResponse($assetsManager->path($application, $slug));
    }

    /**
     * Remove a file from the server
     *
     * @param $application
     * @param $slug
     * @return Response
     * @throws Exception
     */
    public function removeAction($application, $slug)
    {
        $assetsManager = $this->get('app.assets.manager');

        if (!$assetsManager->exists($application, $slug)) {
            throw $this->createNotFoundException(sprintf('File %s not found for the given application %s',
                $slug, $application));
        }
        $assetsManager->remove($application, $slug);

        return new Response();

    }
}
