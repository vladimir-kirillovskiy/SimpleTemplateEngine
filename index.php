<?php 

require("src/Engine.php"); 

$name = "Vladimir";
$stuff = [
    [
        "Thing" => "roses",
        "Desc"  => "red"
    ],
    [
        "Thing" => "violets",
        "Desc"  => "blue"
    ],
    [
        "Thing" => "you",
        "Desc"  => "able to solve this"
    ],
    [
        "Thing" => "we",
        "Desc"  => "interested in you"
    ]
];

$template = new Engine();
$template->render('templates/extra.tmpl', ['Name' => $name, 'Stuff' => $stuff]);

?>