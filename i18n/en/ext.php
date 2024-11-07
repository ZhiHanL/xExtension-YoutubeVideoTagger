<?php

return array(
    'YouTubeVideoTagger' => array(
        'youtube_api_token' => array(
            'label' => 'Youtube API Token',
            'hint' => 'Youtube Data V3 API Token. Details on how to generate one: https://developers.google.com/youtube/registering_an_application',
        ),
        'shorts' => array(
            'duration' => array(
                'label' => 'Shorts duration in seconds',
                'hint' => 'Videos shorter that the configured value will be tagged as a short',
            ),
            'label' =>  'YouTube Shorts Duration',
        ),
    ),
);
