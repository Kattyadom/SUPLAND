<?php
declare(strict_types=1);

function catalog_filters(array $query): array
{
    $filters = [];
    foreach (['q', 'categoria', 'min', 'max', 'stock', 'orden'] as $key) {
        $filters[$key] = is_string($query[$key] ?? null) ? trim($query[$key]) : '';
    }
    $filters['q'] = mb_substr($filters['q'], 0, 200);
    if (!ctype_digit($filters['categoria']) || (int) $filters['categoria'] < 1) {
        $filters['categoria'] = '';
    }
    foreach (['min', 'max'] as $key) {
        if (!preg_match('/^\d{1,6}(?:\.\d{1,2})?$/', $filters[$key])) {
            $filters[$key] = '';
        }
    }
    if ($filters['min'] !== '' && $filters['max'] !== '' && (float) $filters['min'] > (float) $filters['max']) {
        [$filters['min'], $filters['max']] = [$filters['max'], $filters['min']];
    }
    $filters['stock'] = $filters['stock'] === '1' ? '1' : '';
    if (!in_array($filters['orden'], ['recientes', 'precio_asc', 'precio_desc', 'nombre'], true)) {
        $filters['orden'] = 'recientes';
    }
    return $filters;
}

function catalog_products(array $filters): array
{
    $sql = 'SELECT p.*,c.nombre_categoria FROM productos p JOIN categorias c USING(id_categoria) WHERE p.activo=1';
    $values = [];
    foreach (preg_split('/\s+/u', $filters['q'], -1, PREG_SPLIT_NO_EMPTY) ?: [] as $word) {
        // Los signos % y _ se buscan como texto, no como comodines de SQL.
        $pattern = '%' . strtr($word, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
        $sql .= " AND (p.nombre_producto LIKE ? ESCAPE '!' OR p.descripcion LIKE ? ESCAPE '!' OR c.nombre_categoria LIKE ? ESCAPE '!')";
        array_push($values, $pattern, $pattern, $pattern);
    }
    foreach (['categoria' => 'p.id_categoria =', 'min' => 'p.precio >=', 'max' => 'p.precio <='] as $key => $condition) {
        if ($filters[$key] !== '') {
            $sql .= ' AND ' . $condition . ' ?';
            $values[] = $filters[$key];
        }
    }
    if ($filters['stock'] === '1') {
        $sql .= ' AND p.cantidad > 0';
    }
    $orders = ['recientes' => 'p.id_producto DESC', 'precio_asc' => 'p.precio ASC,p.id_producto DESC', 'precio_desc' => 'p.precio DESC,p.id_producto DESC', 'nombre' => 'p.nombre_producto ASC,p.id_producto DESC'];
    return rows($sql . ' ORDER BY ' . $orders[$filters['orden']], $values);
}
