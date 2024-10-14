<?php

namespace App\Entity;

use App\Repository\ZoneElectriqueRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

#[ORM\Entity(repositoryClass: ZoneElectriqueRepository::class)]
class ZoneElectrique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $idZone = null;

    #[ORM\Column(type: 'string', length: 10)]
    private ?string $refZone = null;

    #[ORM\Column(type: 'string', length: 120)]
    private ?string $refSecteur = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $specificite = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $lieux = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $postes = null;

    #[ORM\Column(type: 'integer')]
    private ?int $degree = null;

    #[ORM\Column(type: "polygon", options: ["geometry_type" => "POLYGON"])]
    private $coord;


    public static function getPlaceNames($bounds)
    {
        // Interroger l'API Overpass d'OpenStreetMap
        $overpassUrl = 'https://overpass-api.de/api/interpreter';
    
        // Construire la requête OSM pour obtenir les noms des lieux spécifiques (type place)
        $query = "
            [out:json];
            (
            node[\"place\"]({$bounds['south']},{$bounds['west']},{$bounds['north']},{$bounds['east']});
            way[\"place\"]({$bounds['south']},{$bounds['west']},{$bounds['north']},{$bounds['east']});
            relation[\"place\"]({$bounds['south']},{$bounds['west']},{$bounds['north']},{$bounds['east']});
            );
            out body;
        ";
    
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => $query
            ]
        ];
    
        $context = stream_context_create($options);
        $result = file_get_contents($overpassUrl, false, $context);
    
        if ($result === FALSE) {
            return json_encode(['error' => 'Error occurred while querying Overpass API'], JSON_PRETTY_PRINT);
        }
    
        $data = json_decode($result, true);
    
        return self::extractInfoPlaces($data);
    }
    
    public static function extractInfoPlaces($data)
    {
        $places = [];
        $lieux = "";
    
        if (isset($data['elements']) && is_array($data['elements'])) {
            foreach ($data['elements'] as $element) {
                if (isset($element['tags']['name']) && isset($element['tags']['place'])) {
                    $place = [
                        'name' => $element['tags']['name'],
                        'type' => $element['tags']['place'],
                        'id' => $element['id']
                    ];

                    $places[] = $place;
                    $lieux .= $element['tags']['name']."-";
                }
            }
        }

        // Vérifie si $lieux est vide et ajoute "inconnu" si nécessaire
        if (empty($lieux)) {
            $places[] = $place = [
                'name' => 'Inconnu',
                'type' => null,
                'id' => null
            ];
            $lieux .= "Inconnu";
        }
    
        return [
            "places" => $places,
            "places_str" => rtrim($lieux, "-")
        ];
    }

    
    public static function getSpecificities($bounds)
    {
        // Interroger l'API Overpass d'OpenStreetMap
        $overpassUrl = 'https://overpass-api.de/api/interpreter';

        // Construire la requête OSM avec les bounds
        $query = "
            [out:json];
            (
            node[\"amenity\"=\"prison\"]({$bounds['south']},{$bounds['west']},{$bounds['north']},{$bounds['east']});
            node[\"amenity\"=\"hospital\"]({$bounds['south']},{$bounds['west']},{$bounds['north']},{$bounds['east']});
            );
            out;
        ";

        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => $query
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($overpassUrl, false, $context);

        if ($result === FALSE) {
            return json_encode(['error' => 'Error occurred while querying Overpass API'], JSON_PRETTY_PRINT);
        }

        $data = json_decode($result, true);

        return ZoneElectrique::countFromResult($data);
    }

    public static function countFromResult($data) {
        $countPrisons = 0;
        $countHospitals = 0;
        $degree = 0;

        if (isset($data['elements']) && is_array($data['elements'])) {
            foreach ($data['elements'] as $element) {
                if (isset($element['tags']['amenity'])) {
                    if ($element['tags']['amenity'] === 'prison') {
                        $countPrisons++;
                        $degree++;
                    } elseif ($element['tags']['amenity'] === 'hospital') {
                        $countHospitals++;
                        $degree++;
                    }
                }
            }
        }
        return [
            'degree' => $degree,
            'specificite' => "hopital:".$countHospitals.";prison:".$countPrisons,
        ]; 
    }

    public function init(
        ?int $idZone, 
        ?string $ref_secteur,
        ?string $ref_zone, 
        ?string $specificite, 
        ?string $lieux, 
        ?string $postes, 
        ?int $degree, 
        $coord
    ): self {
        $this->idZone = $idZone;
        $this->refSecteur = $ref_secteur;
        $this->refZone = $ref_zone;
        $this->specificite = $specificite;
        $this->lieux = $lieux;
        $this->postes = $postes;
        $this->degree = $degree;
        $this->coord = $coord;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->idZone;
    }

    public function setId(int $id): void
    {
        $this->idZone = $id;
    }

    public function getRefZone(): ?string
    {
        return $this->refZone;
    }

    public function setRefZone(string $refZone): self
    {
        $this->refZone = $refZone;

        return $this;
    }

    public function getRefSecteur(): ?string
    {
        return $this->refSecteur;
    }

    public function setRefSecteur(string $refSecteur): self
    {
        $this->refSecteur = $refSecteur;

        return $this;
    }

    public function getSpecificite(): ?string
    {
        return $this->specificite;
    }

    public function setSpecificite(string $specificite): self
    {
        $this->specificite = $specificite;

        return $this;
    }

    public function getLieux(): ?string
    {
        return $this->lieux;
    }

    public function setLieux(string $lieux): self
    {
        $this->lieux = $lieux;

        return $this;
    }

    public function getPostes(): ?string
    {
        return $this->postes;
    }

    public function setPostes(string $postes): self
    {
        $this->postes = $postes;

        return $this;
    }

    public function getDegree(): ?int
    {
        return $this->degree;
    }

    public function setDegree(int $degree): self
    {
        $this->degree = $degree;

        return $this;
    }

    // Definir la prioritee du zone geographique en fonction des specificites enregistree
    public function initDegree(): void { 
        // Définir les clés spéciales à vérifier
        $specialKeys = ['hopital', 'prison'];
        
        // Démolier la chaîne en tableaux associatifs
        $specArray = explode(';', $this->specificite);
        
        // Parcourir chaque élément du tableau
        foreach ($specArray as $item) {
            list($key, $value) = explode(':', $item);
            
            // Vérifier si la clé est dans les clés spéciales et que sa valeur est supérieure à 0
            if (in_array(strtolower($key), $specialKeys) && (int)$value > 0) {
                $this->degree = 2; // Arreter 2 si une clé spéciale avec une valeur positive est trouvée
                return;
            }
        }
    }

    public function getCoord()
    {
        return $this->coord;
    }

    public function setCoord($coord): self
    {
        $this->coord = $coord;
        return $this;
    }
}
