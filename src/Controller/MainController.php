<?php

namespace App\Controller;

use App\KeePassXC;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index(): Response
    {
        return $this->render('base.html.twig');
    }

    /**
     * @Route("/root/{path}", name="app_root_path", requirements={"path"=".+"})
     */
    public function root($path = ''): Response
    {
        $kpx = new KeePassXC(
            $this->getParameter('app.kdbx_filename'),
            $this->getParameter('app.kdbx_password'),
            $this->getParameter('app.kdbx_timeout')
        );
        $list = $kpx->getList($path);

        $items = [];
        foreach ($list as $item) {
            $type = 'e';
            if (mb_substr($item, -1) === '/') {
                $type = 'f';
            }

            $items[] = [
                'title' => rtrim($item, '/'),
                'type' => $type
            ];
        }

         usort($items, static function ($a, $b) {
             if ($a['type'] === $b['type']) {
                 return $a['title'] <=> $b['title'];
             }

             return $b['type'] <=> $a['type'];
         });

        $pathList = explode('/', $path);

        if ($path) {
            $path = trim($path,"/") . '/';
        }

        return $this->render('main/index.html.twig', compact('items', 'path', 'pathList'));
    }

    /**
     * @Route("/view/{path}", name="app_item_view", requirements={"path"=".+"})
     */
    public function view($path = ''): Response
    {
        $kpx = new KeePassXC(
            $this->getParameter('app.kdbx_filename'),
            $this->getParameter('app.kdbx_password'),
            $this->getParameter('app.kdbx_timeout')
        );
        $params = $kpx->show($path);

        return $this->render('main/view.html.twig', compact('params'));
    }
}
