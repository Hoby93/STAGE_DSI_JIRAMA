<?php

namespace App\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class PolygonType extends Type
{
    const POLYGON = 'polygon';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'POLYGON';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        // Utiliser unpack pour extraire les données du polygone forme binaire
        $data = unpack('x/x/x/x/corder/Ltype/d*', $value);

        $coordinates = [];

        // Recuperer les coordonnees
        // On compte a partir de 2 prcq les autre donnees sont des val. sup. util pour le decodage
        for ($i = 2; $i < count($data)-2; $i += 2) {
            $coordinates[] = [
                'latitude' => $data[$i],
                'longitude' => $data[$i + 1],
            ];
        }

        return $coordinates;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        // Convertir les coordonnées en valeur binaire
        // Ici, vous devez implémenter la logique pour convertir vos coordonnées en format binaire
        // Cette partie peut varier en fonction de la façon dont vous stockez vos données géographiques

        return pack('xxxxcLdd', 0, 1, $value['latitude'], $value['longitude']);
    }

    public function getName()
    {
        return self::POLYGON;
    }

    // Méthode statique pour convertir les coordonnées
    public static function convert($coord)
    {
        if ($coord === null) {
            return null;
        }

        // Exemple de décodage basé sur le format de vos données
        $data = unpack('x/x/x/x/corder/Ltype/d*', $coord);

        $coordinates = [];
        for ($i = 2; $i < count($data) - 2; $i += 2) {
            $coordinates[] = [
                'latitude' => $data[$i],
                'longitude' => $data[$i + 1],
            ];
        }

        return $coordinates;
    }

    public static function convertLatLngToPolygon($coords_str) {
        // Étape 1: Diviser la chaîne en points individuels
        $points = explode('),', $coords_str);

        $polygon = [];

        // Étape 2: Parcourir chaque point et extraire lat et lng
        foreach ($points as $point) {
            // Supprimer le préfixe "LatLng(" du premier point
            $point = str_replace('LatLng(', '', $point);
            
            // Extraire les valeurs de lat et lng
            list($lat, $lng) = explode(',', $point);
            
            // Supprimer la parenthèse finale du dernier point
            $lng = rtrim($lng, ')');
            
            // Ajouter au tableau polygon
            $polygon[] = ['latitude' => floatval($lat), 'longitude' => floatval($lng)];
        }
        $polygon[] = $polygon[0]; // Fermer le polygone en ajoutant le premier point à la fin
    
        return $polygon;
    }

    public static function convertLatLngToPolygonWKT($coords_str) {
        // Étape 1: Diviser la chaîne en points individuels
        $points = explode('),', $coords_str);

        $polygon = [];
        $polygon_str = 'POLYGON((';

        // Étape 2: Parcourir chaque point et extraire lat et lng
        foreach ($points as $point) {
            // Supprimer le préfixe "LatLng(" du premier point
            $point = str_replace('LatLng(', '', $point);
            
            // Extraire les valeurs de lat et lng
            list($lat, $lng) = explode(',', $point);
            
            // Supprimer la parenthèse finale du dernier point
            $lng = rtrim($lng, ')');
            
            // Ajouter au tableau polygon
            $polygon[] = ['latitude' => floatval($lat), 'longitude' => floatval($lng)];
            $polygon_str .= floatval($lng) . ' ' . floatval($lat) . ', ';
        }
        $polygon_str .= $polygon[0]['longitude'] . ' ' . $polygon[0]['latitude'] . '))';
    
        return $polygon_str;
    }

    public static function convertJsonToPolygon($coordsJson) {
        $coordsArray = json_decode($coordsJson, true);

        $polygon = [];
        //$polygon_str = 'POLYGON((';
        foreach ($coordsArray[0] as $point) {
            $polygon[] = [
                'latitude' => $point['lat'], 
                'longitude' => $point['lng']
            ];
            //$polygon_str .= $point['lng'] . ' ' . $point['lat'] . ', ';
        }
        // Fermer le polygone en ajoutant le premier point à la fin
        //$polygon_str .= $polygon[0]['longitude'] . ' ' . $polygon[0]['latitude'] . '))'; 
        return $polygon;
    }

    public static function convertJsonToPolygonWKT($coordsJson) {
        $coordsArray = json_decode($coordsJson, true);

        $polygon = [];
        $polygon_str = 'POLYGON((';
        foreach ($coordsArray[0] as $point) {
            $polygon[] = [
                'latitude' => $point['lat'], 
                'longitude' => $point['lng']
            ];
            $polygon_str .= $point['lng'] . ' ' . $point['lat'] . ', ';
        }
        // Fermer le polygone en ajoutant le premier point à la fin
        $polygon_str .= $polygon[0]['longitude'] . ' ' . $polygon[0]['latitude'] . '))'; 
        return $polygon_str;
    }

    public static function findMinMaxCoordinates($coords_str) {
        // Diviser la chaîne en points individuels
        $points = explode('),', $coords_str);
    
        // Initialiser les variables pour stocker les coordonnées maximales et minimales
        $bounds = ['north' => null, 'south' => null, 'east' => null, 'west' => null];
    
        // Parcourir chaque point et mettre à jour les coordonnées maximales et minimales
        foreach ($points as $point) {
            list($lat, $lng) = explode(',', str_replace('LatLng(', '', $point));
            $lng = rtrim($lng, ')');
    
            foreach ($bounds as $key => $value) {
                if ($bounds[$key] === null || ($key === 'north' && $lat > $bounds['north']) ||
                    ($key === 'south' && $lat < $bounds['south']) ||
                    ($key === 'east' && $lng > $bounds['east']) ||
                    ($key === 'west' && $lng < $bounds['west'])) {
                    $bounds[$key] = $key === 'north' || $key === 'south' ? $lat : $lng;
                }
            }
        }
    
        return $bounds;
    }
}
