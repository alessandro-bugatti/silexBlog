<?php

// Add the autoloading mechanism of Composer
require_once __DIR__.'/vendor/autoload.php';

//Add the JsonResponse from the library
use Symfony\Component\HttpFoundation\JsonResponse;
//Add the Request  and Response from the library
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;



//Include RedBeanPHP file
include 'rb.php';

//Setup the connection to the MySQL database
R::setup( 'mysql:host=localhost;dbname=blog',
        'guest', 'guest' );

// Create the Silex application
$app = new Silex\Application();

// Add the configuration, etc. here
$app['debug'] = true;

//Preprocessing application/json data to insert them in
//the request object

$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

//First route to an html page with the titles of the articles,
//not the REST API, only as an example
$app->get('/articles.html', function ()  {
    $output = '';
    $articles = R::findAll('article');
    foreach ($articles as $article) { // Create a basic list of article titles
        $output .= "<em>Title:</em> " . $article['title'];
        $output .= '<br>';
    }
    return $output; // Return it to so it gets displayed by the browser
});

//Second route to all the articles as a JSON resource
$app->get('/articles', function ()  {
    $articles_beans = R::findAll('article');
    foreach ($articles_beans as $article)
      $articles_list[] = $article->export();
    return new JsonResponse($articles_list); // Return it as a JSON resource
});

//Route to an article identified by id as a JSON resource
$app->get('/articles/{id}', function ($id)  { // Match the root route (/) and supply the application as argument
    $article = R::findOne( 'article', ' id = ? ', [ $id ] );
    if ($article == NULL)
      $app->abort(404, "Article not found");
    return new JsonResponse($article->export()); // Return it to so it gets displayed by the browser
});


//Route to a range of ids, shows how to manage get parameters
//in this case start and end in the form of
//?start=1&end=3 (example)
$app->get('/articlesRange', function (Request $request)  { // Match the root route (/) and supply the application as argument
    $params = $request->query->all();
    $article_beans = R::find( 'article', ' id >= ? AND id <= ? ',[$params['start'], $params['end']]);
    foreach ($article_beans as $article)
      $articles_list[] = $article->export();
    return new JsonResponse($articles_list); // Return it to so it gets displayed by the browser
});


//Route to create an article
$app->post('/articles', function (Request $request) {
    $article = R::dispense('article');
    //Manage application/x-www-form-urlencoded content-type
    //as they came from a form
    if (0 === strpos($request->headers->get('Content-Type'), 'application/x-www-form-urlencoded'))
    {
      $article->title = $request->get('title');
      $article->text = $request->get('text');
      $article->creation_date = $request->get('creation_date');
    }
    //Manage application/json content-type
    else if (0 === strpos($request->headers->get('Content-Type'), 'application/json')){
      $article->title = $request->request->get('title');
      $article->text = $request->request->get('text');
      $article->creation_date = $request->request->get('creation_date');
    }
    R::store($article);
    return new Response($article->export(),201);
});

//Route to update an article
$app->put('/articles/{id}', function (Request $request, $id) {
    $article = R::load('article',$id);
    //Manage application/x-www-form-urlencoded content-type
    //as they came from a form
    if (0 === strpos($request->headers->get('Content-Type'), 'application/x-www-form-urlencoded'))
    {
      $article->title = $request->get('title');
      $article->text = $request->get('text');
      $article->creation_date = $request->get('creation_date');
    }
    //Manage application/json content-type
    else if (0 === strpos($request->headers->get('Content-Type'), 'application/json')){
      $article->title = $request->request->get('title');
      $article->text = $request->request->get('text');
      $article->creation_date = $request->request->get('creation_date');
    }
    R::store($article);
    return new JsonResponse($article->export(),200);
});

//Route to delete an article
$app->delete('/articles/{id}', function ($id) {
    $article = R::load('article',$id);
    R::trash($article);
    $cancelled["id"]=$id;
    return new JsonResponse($cancelled,200);
});

// This should be the last line
$app->run(); // Start the application, i.e. handle the request
?>
