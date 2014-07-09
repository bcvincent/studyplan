<?php

echo get_string('intro_part1','studyplan');
echo get_string('intro_part2','studyplan');
echo get_string('intro_part3','studyplan');
echo get_string('intro_part4','studyplan');
echo get_string('intro_part5','studyplan');

// extra content (not in English translation)
if ( get_string('intro_part6','studyplan') != '') {
    echo get_string('intro_part6','studyplan');
}

if ( get_string('intro_part7','studyplan') != '') {
    echo get_string('intro_part7','studyplan');
}