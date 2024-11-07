# FreshRSS Youtube Video Tagger

Automatically prefix youtube videos with [Shorts] \ [Upcoming] \ [Live] to make it easier to filter out different types of youtube videos. 

## Notes
A Youtube Data v3 API token is required for this extension to work. Details on how to do so can be found here: https://developers.google.com/youtube/registering_an_application

Shorts are detected by comparing the video duration to a configurable value. Any video added to the feed that is shorter that the `Shorts duration in seconds` config will be labelled as a short.

This project is based off of the work done here! https://github.com/cn-tools/cntools_FreshRssExtensions/tree/master/xExtension-YouTubeChannel2RssFeed


## Installation

To install an extension, download the extension archive first and extract it on your PC. Then, upload the specific extension(s) you want on your server.

Extensions must be in the ./extensions directory of your FreshRSS installation.

## Change log
### v0.0.1 (2024-11-06)
- Initial project setup
