<?php
function calculateDistanceAndAzimuth($imagePath, $droneHeightMeters) {
    // Load the image
    $image = imagecreatefromjpeg($imagePath);
    $width = imagesx($image);
    $height = imagesy($image);

    // Center of the image
    $centerX = $width / 2;
    $centerY = $height / 2;

    // Define the color range for red
    $lowerRed = [200, 0, 0];
    $upperRed = [255, 50, 50];

    // Find the red pixel coordinates
    $redPoints = [];
    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            $rgb = imagecolorat($image, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;

            if ($r >= $lowerRed[0] && $r <= $upperRed[0] &&
                $g >= $lowerRed[1] && $g <= $upperRed[1] &&
                $b >= $lowerRed[2] && $b <= $upperRed[2]) {
                $redPoints[] = ['x' => $x, 'y' => $y];
            }
        }
    }

    // Calculate the pixel size in meters
    $pixelSizeMeters = $droneHeightMeters / $height;

    // Array to hold unique directions and their azimuths
    $uniqueDirections = [];

    // Process each red point
    foreach ($redPoints as $index => $point) {
        $redX = $point['x'];
        $redY = $point['y'];

        // Calculate the distance in pixels
        $distancePixels = sqrt(pow($redX - $centerX, 2) + pow($redY - $centerY, 2));

        // Convert distance to meters
        $distanceMeters = $distancePixels * $pixelSizeMeters;

        // Calculate the azimuth
        $azimuth = atan2($redY - $centerY, $redX - $centerX) * (180 / M_PI);
        if ($azimuth < 0) {
            $azimuth += 360;
        }

        // Determine the direction (clock orientation)
        $angle = atan2($redY - $centerY, $redX - $centerX) * (180 / M_PI);
        if ($angle < 0) {
            $angle += 360;
        }

        $clockPosition = "";
        if ($angle >= 0 && $angle < 30) $clockPosition = "3 година";
        else if ($angle >= 30 && $angle < 60) $clockPosition = "4 година";
        else if ($angle >= 60 && $angle < 90) $clockPosition = "5 година";
        else if ($angle >= 90 && $angle < 120) $clockPosition = "6 година";
        else if ($angle >= 120 && $angle < 150) $clockPosition = "7 година";
        else if ($angle >= 150 && $angle < 180) $clockPosition = "8 година";
        else if ($angle >= 180 && $angle < 210) $clockPosition = "9 година";
        else if ($angle >= 210 && $angle < 240) $clockPosition = "10 година";
        else if ($angle >= 240 && $angle < 270) $clockPosition = "11 година";
        else if ($angle >= 270 && $angle < 300) $clockPosition = "12 година";
        else if ($angle >= 300 && $angle < 330) $clockPosition = "1 година";
        else if ($angle >= 330 && $angle < 360) $clockPosition = "2 година";

        // Store unique directions and azimuths
        if (!isset($uniqueDirections[$clockPosition])) {
            $uniqueDirections[$clockPosition] = [
                'distance' => $distanceMeters, 
                'azimuth' => $azimuth, 
                'count' => 1
            ];
        } else {
            $uniqueDirections[$clockPosition]['distance'] += $distanceMeters;
            $uniqueDirections[$clockPosition]['azimuth'] += $azimuth;
            $uniqueDirections[$clockPosition]['count'] += 1;
        }
    }

    // Initialize the result string
    $result = "";

    // Write average distance and azimuth for each unique direction
    foreach ($uniqueDirections as $direction => $data) {
        $averageDistance = $data['distance'] / $data['count'];
        $averageAzimuth = $data['azimuth'] / $data['count'];
        $result .= "Орієнтир: " . $direction . " Середня відстань: " . $averageDistance . " meters, Азимут: " . $averageAzimuth . " градусів\n";
    }

    return $result;
}

$imagePath = '10.jpg'; // Replace with your image path
$droneHeightMeters = 10; // Drone height in meters

$result = calculateDistanceAndAzimuth($imagePath, $droneHeightMeters);

// Write the result to a text file
$file = 'result.txt';
file_put_contents($file, $result);

echo "Result written to " . $file;
?>
