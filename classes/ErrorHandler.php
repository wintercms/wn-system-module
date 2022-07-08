<?php namespace System\Classes;

use Throwable;
use Cms\Classes\Theme;
use Cms\Classes\Router;
use Cms\Classes\Controller as CmsController;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;
use Winter\Storm\Support\Facades\Config;
use Winter\Storm\Exception\ErrorHandler as ErrorHandlerBase;

/**
 * System Error Handler, this class handles application exception events.
 *
 * @package winter\wn-system-module
 * @author Alexey Bobkov, Samuel Georges
 */
class ErrorHandler extends ErrorHandlerBase
{
    /**
     * Looks up an error page using the CMS route "/error". If the route does not
     * exist, this function will use the error view found in the Cms module.
     * @return mixed Error page contents.
     */
    public function handleCustomError()
    {
        if (Config::get('app.debug', false)) {
            return null;
        }

        if (class_exists(Theme::class) && in_array('Cms', Config::get('cms.loadModules', []))) {
            $theme = Theme::getActiveTheme();
            $router = new Router($theme);

            // Use the default view if no "/error" URL is found.
            if (!$router->findByUrl('/error')) {
                return View::make('cms::error');
            }

            // Route to the CMS error page.
            $controller = new CmsController($theme);
            $result = $controller->run('/error');
        } else {
            $result = View::make('system::error');
        }

        // Extract content from response object
        if ($result instanceof Response) {
            $result = $result->getContent();
        }

        return $result;
    }

    /**
     * Displays the detailed system exception page.
     *
     * @return \Illuminate\View\View|string Object containing the error page.
     */
    public function handleDetailedError(Throwable $exception)
    {
        // Ensure System view path is registered
        View::addNamespace('system', base_path().'/modules/system/views');

        /** @var \Illuminate\View\View */
        $view = View::make('system::exception', ['exception' => $exception]);

        return $view;
    }
}
