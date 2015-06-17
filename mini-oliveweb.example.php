<?php
    /*
     * Mini-OliveWeb 1.0.x Example
     * OliveTech, June 2015
     * A Luke Bullard Project
     */
    
    //either require mini-oliveweb.php or place it's contents here.
    require("mini-oliveweb.php");
    
    //initialize Mini-OliveWeb
    Olive::initialize();
    
    /*
     * Continue like a normal OliveWeb page.
     * Make sure to use the GET P variable for url parameters.
     * This means if the full OliveWeb router matched (through regex) /example/123/abc
     * one must request the following URL with Mini-OliveWeb instead: example.php?P=123/abc
     */
    
    //start up the Modules system
    $modules = Modules::getInstance();
?>