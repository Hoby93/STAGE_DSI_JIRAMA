<?php 

namespace App\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class PointType extends Type
{
    const POINT = 'point';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'POINT';
    }

    public static function convert($coord)
    {
        if ($coord === null) {
            return null;
        }

        // Convertir la valeur binaire en chaîne de caractères
        $data = unpack('x/x/x/x/corder/Ltype/dlat/dlon', $coord);
        return ['latitude' => $data['lat'], 'longitude' => $data['lon']];
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        // Convertir la valeur binaire en chaîne de caractères
        $data = unpack('x/x/x/x/corder/Ltype/dlat/dlon', $value);
        return ['latitude' => $data['lat'], 'longitude' => $data['lon']];
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        // Convertir les coordonnées en valeur binaire pour MySQL
        return pack('xxxxcLdd', 0, 1, (double) $value['latitude'], (double) $value['longitude']);
    }

    public function getName()
    {
        return self::POINT;
    }
}
