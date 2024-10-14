SELECT 
    id_infra,
    ref_infra,
    libelle,
    ST_Distance_Sphere(coord, ST_PointFromText('POINT(-18.9179175 47.531714)')) / 1000 AS distance_km
FROM 
    infrastructure
ORDER BY distance_km ASC;


SELECT i.type_infra, libelle, i.coord
FROM infrastructure i
JOIN (
    SELECT type_infra, MIN(ST_Distance_Sphere(coord, ST_PointFromText('POINT(-18.9179175 47.531714)'))) AS min_distance
    FROM infrastructure
    GROUP BY type_infra
) t ON i.type_infra = t.type_infra
   AND ST_Distance_Sphere(coord, ST_PointFromText('POINT(-18.9179175 47.531714)')) = t.min_distance;