<?php

    require_once __DIR__."/../vendor/autoload.php";
    require_once __DIR__."/../src/Habit.php";
    require_once __DIR__."/../src/Project.php";
    require_once __DIR__."/../src/Journal.php";
    require_once __DIR__."/../src/Step.php";
    require_once __DIR__."/../src/User.php";


    use Symfony\Component\Debug\Debug;
    Debug::enable();

    $app = new Silex\Application();

    $app['debug'] = true;


    $server = 'mysql:host=localhost;dbname=lifecoach';
    $username = 'root';
    $password = '';
    $DB = new PDO($server, $username, $password);


    use Symfony\Component\HttpFoundation\Request;
    Request::enableHttpMethodParameterOverride();


    session_start();


    // Create default Project for all users for adding Chores
    // populate it with (description,motivation,due_date,priority)
    // if(empty(Project::getAll())) {
    //     $default_project = new Project("Chores",null,"0000-00-00",0);
    //     $default_project->save();
    // }


    $app->register(new Silex\Provider\TwigServiceProvider(), array(
        'twig.path' => __DIR__.'/../views'
    ));

    // Set timezone for date formatting
    $app['twig']->getExtension('core')->setTimezone('America/Los_Angeles');


    //Home page
    $app->get('/', function() use ($app){
      return $app['twig']->render('sign_in.html.twig');
    });

    $app->get('/dashboard', function() use ($app) {
        unset($_SESSION['pcount']);
        $_SESSION['pcount'] = Project::countProjects();
        $new_user = new User("","");
        $user = $new_user->authenticate($_SESSION['gemail']);
        return $app['twig']->render('dashboard.html.twig', array('user' => $user,'project_number' => $_SESSION['pcount']));
    });

    $app->post("/dashboard", function() use ($app) {
        unset($_SESSION['gemail']);
        $_SESSION['gemail'] = $_POST['email'];
        $password = $_POST['password'];
        $new_user = new User("",preg_quote($_POST['email'], "'"),preg_quote($_POST['password'], "'") );
        $user = $new_user->authenticate($_SESSION['gemail']);
        if ( $user != null ) {
            $_SESSION['pcount'] = Project::countProjects();
            return $app['twig']->render('dashboard.html.twig', array('user' => $user,'project_number' => $_SESSION['pcount']));
        }
        else { return $app['twig']->render('error.html.twig'); }
    });




    // Include Other Routes
    require_once __DIR__."/../routes/coach_new_project.php";
    require_once __DIR__."/../routes/coach_active_project.php";
    require_once __DIR__."/../routes/coach_new_habit.php";
    require_once __DIR__."/../routes/coach_active_habit.php";
    require_once __DIR__."/../routes/habit.php";
    require_once __DIR__."/../routes/journal.php";
    require_once __DIR__."/../routes/project.php";
    require_once __DIR__."/../routes/user.php";



    return $app;
?>
