<?php
include 'rb.php';

R::setup( 'mysql:host=localhost;dbname=blog',
        'guest', 'guest' );

$article = R::dispense( 'article' );
$article->title = 'Brilliance';
$article->text = 'A good book';
$article->creation_date = '2016-04-27';
$id = R::store( $article );

$article = R::dispense( 'article' );
$article->title = 'Pilgrim';
$article->text = 'Another good book';
$article->creation_date = '2016-04-28';
$id = R::store( $article );
