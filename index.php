<?php
	
	// Application flag
    define('ARCANE', true); // TODO: Require this to be true for all parts of ArcaneCMS.

    date_default_timezone_set('America/Chicago'); //TODO: Make this a config option.

    // Determine our absolute document root
    define('DOC_ROOT', realpath(dirname(__FILE__)));
    define('DS', DIRECTORY_SEPARATOR);
    if(!file_exists(DOC_ROOT.'includes/class.config.php'))
    {
        define('ARCANEINSTALL', true);
        define('WEB_ROOT', ''); // Set this to be relative to $_SERVER['HTTP_HOST'] (No trailing slash)
        define('ARCANE_SITE_URL', 'http://'.$_SERVER['HTTP_HOST'] . WEB_ROOT);
    }
    // Global include files
    require_once DOC_ROOT . '/includes/functions.inc.php';  // __autoload() is contained in this file
    require_once DOC_ROOT . '/includes/class.dbobject.php'; // DBOBject...
    require_once DOC_ROOT . '/includes/class.objects.php';  // and its subclasses

    // Fix magic quotes
    if(get_magic_quotes_gpc())
    {
        $_POST    = fix_slashes($_POST);
        $_GET     = fix_slashes($_GET);
        $_REQUEST = fix_slashes($_REQUEST);
        $_COOKIE  = fix_slashes($_COOKIE);
    }

    // Load our config settings
    $Config = Config::getConfig();

    // Store session info in the database?
    if(Config::get('useDBSessions') === true)
        if(!ARCANEINSTALL) DBSession::register();

    // Initialize our session
    session_name('arcanesesh');
    session_start();
	
    // Initialize current user
    $Auth = Auth::getInstance();
	// If you need to bootstrap a first user into the database, you can run this line once
    //Auth::createNewUser('admin', 'password', 'admin'); //DEBUG purposes only, obviously not a safe option.

    // Object for tracking and displaying error messages
    $Error = Error::getInstance();

    // Initialize ThemeEngine (Creates a singleton ThemeEngine)
    $ThemeEngine = ThemeEngine::getInstance();
	
    // Initialize HTTPError for handling HTTP status codes such as 404.
    $HTTPError = HTTPError::getInstance();

    // Initialize Router for handling all requests
    $Router = Router::getInstance();
	
    // Add custom routes here
    // e.g. $router->map('/about', array('controller' => 'node', 'action' => 'view', 'id' => 'about')); // Effectively executes Node::View('about');
	
    // START MF>>>
    $Router->map('/about', array('controller' => 'node', 'action' => 'view', 'id' => 'about'));
    $Router->map('/portfolio', array('controller' => 'node', 'action' => 'view', 'id' => 'portfolio'));
    $Router->map('/projects', array('controller' => 'node', 'action' => 'view', 'id' => 'projects'));
    // <<<END MF
	
    $Router->default_routes(); // These routes have lower precedence than the custom routes.
    $Router->execute(); // Commits the mapped routes.
	
    if($Router->route_found)
    {
        $controller = $Router->controller; // will return name as it appears in url, ex: 'user_images'
        $action = $Router->action;
        $params = $Router->params; // array(...)
        $controller = ucfirst($controller);
        $contConstruct = 'getInstance';
        $do = $controller::$contConstruct();
        if(!empty($params)) { // id is also params[0] or params['id']
            $do->$action($params);
        } else {
            $do->$action();
        }
    } else {
        $HTTPError->trigger('404'); // The route is invalid, so the page can not be found.
    }
?>
