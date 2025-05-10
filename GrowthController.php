<?php
require_once __DIR__ . '/../models/GrowthMeasurement.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/AuthMiddleware.php';

class GrowthController {
    public function addMeasurement($data) {
        $user = AuthMiddleware::authenticate();
        
        if (empty($data['measurement_date']) || (empty($data['height']) && empty($data['weight']))) {
            Response::send(400, 'At least height or weight is required with measurement date');
        }
        
        $measurement = new GrowthMeasurement();
        $measurement->user_id = $user->sub;
        $measurement->height = $data['height'] ?? null;
        $measurement->weight = $data['weight'] ?? null;
        $measurement->measurement_date = $data['measurement_date'];
        $measurement->notes = $data['notes'] ?? null;
        
        // Calculate BMI if both height and weight are provided
        if ($measurement->height && $measurement->weight) {
            $heightInMeters = $measurement->height / 100;
            $measurement->bmi = $measurement->weight / ($heightInMeters * $heightInMeters);
        }
        
        if ($measurement->create()) {
            Response::send(201, 'Growth measurement added successfully');
        } else {
            Response::send(500, 'Failed to add growth measurement');
        }
    }
    
    public function getMeasurements() {
        $user = AuthMiddleware::authenticate();
        
        $measurement = new GrowthMeasurement();
        $measurement->user_id = $user->sub;
        
        $measurements = $measurement->read();
        
        Response::send(200, 'Growth measurements retrieved', $measurements);
    }
    
    public function getLatestMeasurements() {
        $user = AuthMiddleware::authenticate();
        
        $measurement = new GrowthMeasurement();
        $measurement->user_id = $user->sub;
        
        $latest = $measurement->getLatest();
        
        Response::send(200, 'Latest growth measurements retrieved', $latest);
    }
    
    public function deleteMeasurement($id) {
        $user = AuthMiddleware::authenticate();
        
        $measurement = new GrowthMeasurement();
        $measurement->id = $id;
        $measurement->user_id = $user->sub;
        
        if ($measurement->delete()) {
            Response::send(200, 'Growth measurement deleted successfully');
        } else {
            Response::send(500, 'Failed to delete growth measurement');
        }
    }
}
?>