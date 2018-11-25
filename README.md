# E2S Web Interface

**E2S** is a FFmpeg-based light web interface for streaming live video from Enigma 2 based STBs, optimized for use on mobile devices. It runs on Windows, but could be fairly easily ported to other operating systems by changing the code for interacting with FFmpeg. <br/>

The interface can be set up in two ways:
- as a standalone windows installation, which includes everything needed for running out of the box (recommended for beginners) or
- as a PHP script - you need to have your own web server with at least PHP 5.6 running beforehand

## Impotant

By default E2S comes configured with software only H264 encoding/decoding for compatibility reasons - you may tweak the bin\stream.bat file for your particular system by consulting the latest FFmpeg documentation.

The preset transcoding values for resolution and bitrates can be edited in config.php.

To install as an Android application: open E2S's URL with Chrome and click on the "Add to Home Screen" icon at them bottom of the screen. 

## Attribution

E2S includes code from the following open sourse projects:

The Apache HTTP Server Project - https://httpd.apache.org/<br/>
PHP: Hypertext Preprocessor - http://php.net/<br/>
FFmpeg - https://www.ffmpeg.org<br/>
Bootstrap - https://getbootstrap.com/<br/>
Font Awesome - https://fontawesome.com/<br/>
Jquery - https://jquery.org/<br/>
hls.js - https://github.com/video-dev/hls.js<br/>
<br/>
Please note that this software comes with no guarantees - use it only for testing and experimentation.
