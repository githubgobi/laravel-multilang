<?php
/*
 * This file is part of the Laravel MultiLang package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Longman\LaravelMultiLang\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Redirector;
use Longman\LaravelMultiLang\MultiLang as MultiLangLib;

class MultiLang
{
    /**
     * Application.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Redirector.
     *
     * @var \Illuminate\Routing\Redirector
     */
    protected $redirector;

    /**
     * Multilang.
     *
     * @var \Longman\LaravelMultiLang\Multilang
     */
    protected $multilang;

    /**
     * MultiLang constructor.
     *
     * @param \Illuminate\Foundation\Application $app
     * @param \Illuminate\Routing\Redirector $redirector
     * @param \Longman\LaravelMultiLang\MultiLang $multilang
     */
    public function __construct(Application $app, Redirector $redirector, MultiLangLib $multilang)
    {
        $this->app = $app;
        $this->redirector = $redirector;
        $this->multilang = $multilang;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $url = $this->multilang->getRedirectUrl($request);

        if ($url !== null) {
            if ($request->expectsJson()) {
                return response('Not found', 404);
            } else {
                return $this->redirector->to($url);
            }
        }

        $locale = $this->multilang->detectLocale($request);

        $this->app->setLocale($locale);

        if ($this->multilang->getConfig()->get('set_carbon_locale')) {
            Carbon::setLocale($locale);
        }

        if ($this->multilang->getConfig()->get('set_system_locale')) {
            $locales = $this->multilang->getLocales();
            if (! empty($locales[$locale]['full_locale'])) {
                setlocale(LC_ALL, $locales[$locale]['full_locale']);
            }
        }

        return $next($request);
    }
}
