<?php

declare(strict_types=1);

namespace RuthgerIdema\UrlRewrite\Http;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use RuthgerIdema\UrlRewrite\Entities\UrlRewrite;
use RuthgerIdema\UrlRewrite\Repositories\Interfaces\UrlRewriteInterface;

class UrlRewriteController
{
    /** @var UrlRewriteInterface */
    protected $repository;

    public function __construct(
        UrlRewriteInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function __invoke($url): object
    {
        if (! $urlRewrite = $this->repository->getByRequestPath($url)) {
            return view('biolink_not_found');

            $newurlRewrite = new UrlRewrite();
            $newurlRewrite->type = 'biolink';
            $newurlRewrite->type_attributes = ['id'=> 0];
            $newurlRewrite->request_path = $url;
            $newurlRewrite->target_path = '/biolink/0';
            $newurlRewrite->redirect_type = 0;
            $newurlRewrite->description = '[ /'.$url ." ]-->- No Route FOUND ->--[ ". $url ." ]";
            // $newurlRewrite->save();
            if($newurlRewrite->save()){
                return $this->forwardResponse($newurlRewrite->target_path);
                // return redirect($url,'/biolink/0');
            }
            abort(404);
        }

        if ($urlRewrite->isForward()) {
            return $this->forwardResponse($urlRewrite->target_path);
        }
        return redirect($urlRewrite->target_path, $urlRewrite->getRedirectType());
    }

    protected function forwardResponse($url): Response
    {

        return Route::dispatch(
            Request::create(
                '/'.ltrim($url, '/'),
                request()->getMethod()
            )
        );

    }
}
