0007-geospatial-architecture.md


# ADR-0007: Geospatial Architecture

- Status: Accepted
- Date: 2026-09-28
- Decision Type: Architecture
- Scope: Geospatial

## Context

IOMP requires an interactive investment map and geographic discovery capabilities.

Requirements include:

- district boundaries;
- opportunity locations;
- nearby queries;
- bounding-box queries;
- polygon queries;
- GeoJSON;
- cascading filters;
- map markers;
- district boundaries.

## Decision

PostGIS is the authoritative geospatial engine.

MapLibre GL will be used for the primary browser map experience.

Leaflet may be used for appropriate administrative or specialized map interactions where useful, but MapLibre is the preferred primary investment-map technology unless an explicit decision changes this.

## Spatial Data

Districts may contain polygon/multipolygon geometry.

Opportunities may contain point geometry.

Geometries must use a documented spatial reference system.

The implementation must validate coordinate ranges and geometry types.

## Spatial Queries

The API must support:

### Nearby

Return opportunities within a validated distance from a supplied coordinate.

### Bounding Box

Return opportunities within:

- minimum longitude;
- minimum latitude;
- maximum longitude;
- maximum latitude.

### Polygon

Return resources intersecting or contained by a validated polygon according to the approved query semantics.

## Map Query Pattern

The preferred interaction is:

Map viewport
→ bounding box
→ API
→ PostGIS
→ spatial index
→ bounded result
→ GeoJSON
→ MapLibre

The browser must not download the complete opportunity dataset merely to perform viewport filtering.

## Spatial Indexes

Spatial columns must use appropriate GiST indexes.

Query plans should be reviewed for significant spatial queries.

## GeoJSON

GeoJSON is the transport representation for geospatial API responses.

It may also be used for controlled district-boundary import.

Imported GeoJSON is untrusted and must undergo validation before persistence.

## Cascading Filters

The map must support the confirmed filter hierarchy:

Region
→ District
→ Sector
→ Investment Type
→ Status

Additional filters may include investment amount and sorting.

Filter options must be driven by authoritative data and explicit API contracts.

## Authorization

Geospatial visibility must respect the same authorization and visibility rules as non-geospatial opportunity access.

A map must not become a mechanism for bypassing opportunity visibility controls.

## Performance

Spatial queries must:

- be bounded;
- use indexes;
- avoid unnecessary application-memory processing;
- return only required fields;
- support pagination or bounded map results where appropriate.

## Accessibility

The map must not be the only way to discover opportunities.

Accessible list/table alternatives must be available where required.

## Consequences

PostGIS handles spatial computation efficiently while MapLibre provides a modern browser map.

The application must carefully control query bounds and result sizes.

## Related Requirements

FR-01
FR-02
FR-05
FR-06
FR-07

NFR-01
NFR-02
NFR-05


The following SQL and hosting sketch is illustrative only. It does not decide the nearby radius, the result limit, Amazon RDS as the host, or a table named `locations`. Approved spatial behaviour remains the decision section above. Nearby radius and result limits remain AMB-30.

A typical setup is:

```
Laravel
   │
   ├── Eloquent / Query Builder
   │
   └── spatial SQL
          │
          ▼
   PostgreSQL on Amazon RDS
          │
          └── PostGIS extension
```

Query locations near a point

 This is where PostGIS becomes particularly useful.

 Suppose you want all locations within **5 km** of a user's location.

 You could do:

```
$locations = DB::select("
    SELECT *
    FROM locations
    WHERE ST_DWithin(
        location::geography,
        ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography,
        ?
    )
", [
    $longitude,
    $latitude,
    5000,
]);
```

 The `5000` is metres because we're using the `geography` representation.

 You could also calculate the actual distance:

```
SELECT
    *,
    ST_Distance(
        location::geography,
        ST_SetSRID(
            ST_MakePoint(:longitude, :latitude),
            4326
        )::geography
    ) AS distance
FROM locations
ORDER BY distance;
```

 In Laravel:

```
$locations = DB::select("
    SELECT
        *,
        ST_Distance(
            location::geography,
            ST_SetSRID(
                ST_MakePoint(?, ?),
                4326
            )::geography
        ) AS distance
    FROM locations
    ORDER BY distance
    LIMIT 50
", [
    $longitude,
    $latitude,
]);
```

---

 # 5\. Make the query Eloquent-friendly

 You don't have to abandon Eloquent.

 For example:

```
$locations = Location::query()
    ->select('locations.*')
    ->selectRaw("
        ST_Distance(
            location::geography,
            ST_SetSRID(
                ST_MakePoint(?, ?),
                4326
            )::geography
        ) AS distance
    ", [$longitude, $latitude])
    ->whereRaw("
        ST_DWithin(
            location::geography,
            ST_SetSRID(
                ST_MakePoint(?, ?),
                4326
            )::geography,
            ?
        )
    ", [$longitude, $latitude, 5000])
    ->orderBy('distance')
    ->limit(50)
    ->get();
```          