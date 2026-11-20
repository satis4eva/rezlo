<?php
declare(strict_types=1);

/**
 * Starter catalogue. Every product, price and tier here is a SAMPLE —
 * edit, replace or deactivate them in Admin → Products before launch.
 * Prices are in pence, ex VAT.
 */
function db_seed(): void
{
    $cats = [
        ['flooring', 'Flooring', 'LVT, rigid core, engineered wood, laminate, carpet tiles and safety vinyl for residential and commercial fit-outs.', 'oak:#b8895a', 1],
        ['tiles', 'Tiles', 'Porcelain and ceramic wall and floor tiles, boxed and palletised.', 'tile:#cfcac1', 2],
        ['wall-coverings', 'Wall coverings', 'Commercial vinyl wallcoverings and lining papers by the roll and case.', 'paper:#d8d2c4', 3],
        ['paint', 'Paint and coatings', 'Trade emulsions, contract matt, primers, satinwood and masonry paint in trade sizes.', 'paint:#e9e6dc', 4],
        ['adhesives-prep', 'Adhesives and preparation', 'Tile adhesive, grout, levelling compound, flooring adhesive and underlay.', 'bag:#8f938c', 5],
        ['sundries', 'Decorating sundries', 'Dust sheets, masking tape and roller sleeves in trade packs.', 'roll:#f2c230', 6],
    ];
    $catIds = [];
    foreach ($cats as [$slug, $name, $desc, $swatch, $sort]) {
        $catIds[$slug] = insert('categories', compact('slug', 'name') + ['description' => $desc, 'swatch' => $swatch, 'sort' => $sort]);
    }

    // [cat, sku, name, short, description, unit, pack_note, price, moq, step, pallet, swatch, featured, tiers[min => price]]
    $p = [
        ['flooring', 'RZ-LVT-NOK5', 'LVT click plank, natural oak, 5mm', 'Click-fit luxury vinyl plank with 0.3mm wear layer.', "Waterproof click-fit LVT plank for kitchens, bathrooms and light commercial areas. 1220 × 180mm planks, 5mm overall thickness, 0.3mm wear layer, bevelled edges. Fit floating over a suitable underlay.", 'box', '2.2 m² per box', 3250, 5, 1, 60, 'oak:#b8895a', 1, [20 => 2990, 60 => 2750, 120 => 2520]],
        ['flooring', 'RZ-SPC-GAS6', 'Rigid core SPC plank, grey ash, 6mm', 'Stone-polymer core with attached IXPE underlay.', "Rigid core plank that hides minor subfloor imperfections and stays stable in rooms with temperature changes. 1220 × 228mm, 6mm with 1mm attached underlay, 0.5mm wear layer.", 'box', '1.95 m² per box', 3600, 5, 1, 56, 'oak:#9a9690', 1, [20 => 3320, 56 => 3040]],
        ['flooring', 'RZ-ENG-OAK14', 'Engineered oak 14mm, brushed and oiled', '3mm oak top layer on a multi-ply core.', "Engineered oak boards, 14/3mm, 190mm wide in random lengths. Brushed and oiled finish, micro-bevel, tongue and groove. Suitable for underfloor heating when bonded down.", 'box', '1.86 m² per box', 5800, 3, 1, 45, 'oak:#c79a64', 1, [15 => 5400, 45 => 4950]],
        ['flooring', 'RZ-LAM-SMK8', 'Laminate 8mm AC4, smoked oak', 'AC4 commercial-rated laminate.', "8mm AC4 laminate for high-traffic domestic and moderate commercial use. 1380 × 193mm boards, 4-sided V-groove.", 'box', '2.13 m² per box', 1890, 5, 1, 70, 'oak:#7d6449', 0, [25 => 1680, 70 => 1540]],
        ['flooring', 'RZ-CPT-CHR50', 'Commercial carpet tiles 50×50, charcoal', 'Loop-pile tiles with bitumen backing.', "Heavy-contract loop-pile carpet tiles for offices and retail. 500 × 500mm, 20 tiles per box. Lay with tackifier.", 'box', '5 m² (20 tiles) per box', 4400, 4, 1, 40, 'carpet:#4a4e52', 0, [20 => 3950, 80 => 3600]],
        ['flooring', 'RZ-VNS-R10', 'Safety vinyl sheet 2mm, R10', 'Slip-resistant sheet vinyl, 2m wide.', "2mm safety vinyl for kitchens, wet areas and care settings. Sold per square metre from 2m-wide rolls, cut to length.", 'm²', 'cut from 2m-wide roll', 1420, 10, 1, 0, 'vinyl:#6f8a7c', 0, [50 => 1290, 200 => 1160]],
        ['tiles', 'RZ-POR-WG60', 'Porcelain floor tile 600×600, matt warm grey', 'Rectified, R10, frost resistant.', "Full-body porcelain floor tile, rectified edges, matt finish. Suitable for internal floors and walls.", 'box', '1.44 m² (4 tiles) per box', 2450, 5, 1, 72, 'tile:#bdb7ad', 1, [30 => 2190, 72 => 1980]],
        ['tiles', 'RZ-CER-WG36', 'Ceramic wall tile 300×600, gloss white', 'Rectified white body wall tile.', "Gloss white ceramic wall tile for bathrooms and kitchens. 300 × 600 × 9mm, rectified.", 'box', '1.44 m² (8 tiles) per box', 1680, 5, 1, 80, 'tile:#f3f3ef', 0, [30 => 1490, 80 => 1340]],
        ['wall-coverings', 'RZ-WCV-LIN2', 'Commercial vinyl wallcovering Type 2, linen stone', 'Hard-wearing, wipeable, Class B fire rated.', "Type 2 commercial vinyl wallcovering for corridors, hotels and offices. 1.37m wide, 30m rolls (about 41 m²). Hang with heavy-duty ready-mixed adhesive.", 'roll', '30m × 1.37m', 12900, 1, 1, 0, 'paper:#cfc6b5', 1, [5 => 11800, 15 => 10800]],
        ['wall-coverings', 'RZ-LIN-1400', 'Lining paper 1400 grade', 'Heavy lining paper for walls and ceilings.', "1400 grade lining paper, 10m × 560mm rolls. Sold in cases of 12.", 'roll', 'sold in cases of 12', 320, 12, 12, 0, 'paper:#ece6d8', 0, [48 => 280, 144 => 245]],
        ['paint', 'RZ-VME-BW10', 'Trade vinyl matt emulsion, brilliant white 10L', 'High-opacity trade emulsion, up to 16 m²/L.', "Trade vinyl matt emulsion for new plaster (after mist coat) and redecoration. Low sheen, high opacity, touch-dry in 2 hours.", 'tub', '10 litres', 3450, 2, 1, 48, 'paint:#f7f7f3', 1, [12 => 3120, 48 => 2840]],
        ['paint', 'RZ-CME-MG15', 'Contract matt emulsion, magnolia 15L', 'For new-build and void property work.', "Breathable contract matt for new plaster and void refurbishments. Spray or roller applied.", 'tub', '15 litres', 2990, 2, 1, 44, 'paint:#efe5cf', 0, [12 => 2700, 48 => 2450]],
        ['paint', 'RZ-DMT-PW5', 'Trade durable matt, pure white 5L', 'Scrubbable matt for high-traffic walls.', "Durable, washable matt emulsion for schools, healthcare and commercial corridors.", 'tub', '5 litres', 2640, 2, 1, 96, 'paint:#fbfbf8', 0, [12 => 2390, 48 => 2150]],
        ['paint', 'RZ-SAT-WH25', 'Water-based satinwood, white 2.5L', 'Low-odour satinwood for interior wood and metal.', "Quick-drying, non-yellowing water-based satinwood for doors, skirting and architraves.", 'tin', '2.5 litres', 2480, 2, 1, 0, 'paint:#f4f4f0', 0, [12 => 2240, 36 => 2020]],
        ['paint', 'RZ-MST-10', 'Mist coat primer 10L', 'Primer for new plaster and porous surfaces.', "Breathable mist coat that seals new plaster ready for emulsion.", 'tub', '10 litres', 2760, 2, 1, 48, 'paint:#f0ede6', 0, [12 => 2490, 48 => 2260]],
        ['paint', 'RZ-MAS-PBW10', 'Smooth masonry paint, pure brilliant white 10L', 'Weatherproof exterior masonry finish.', "Smooth exterior masonry paint for render, brick and pebbledash. Rainproof in 1 hour.", 'tub', '10 litres', 3980, 2, 1, 44, 'paint:#ffffff', 0, [12 => 3600, 48 => 3280]],
        ['adhesives-prep', 'RZ-ADH-RSG20', 'Rapid-set flexible tile adhesive 20kg, grey', 'S1 flexible, walk-on in 3 hours.', "Rapid-setting flexible cement-based tile adhesive for walls and floors, including underfloor heating and timber with suitable preparation.", 'bag', '20kg', 1640, 5, 1, 56, 'bag:#8f938c', 1, [25 => 1460, 56 => 1320]],
        ['adhesives-prep', 'RZ-GRT-SG5', 'Flexible wall and floor grout 5kg, silver grey', 'Joints 2–20mm, mould resistant.', "Flexible cementitious grout for wall and floor joints from 2mm to 20mm.", 'bag', '5kg', 980, 4, 1, 0, 'bag:#a7aaa6', 0, [20 => 870, 60 => 790]],
        ['adhesives-prep', 'RZ-SLC-20', 'Self-levelling compound 20kg', 'Feather to 10mm, walk-on in 2–3 hours.', "Fibre-reinforced self-levelling compound for preparing floors before LVT, vinyl, carpet and tiles.", 'bag', '20kg', 1860, 5, 1, 56, 'bag:#b1ada4', 0, [25 => 1640, 56 => 1490]],
        ['adhesives-prep', 'RZ-ADL-PS15', 'Pressure-sensitive LVT adhesive 15L', 'Solvent-free, for LVT and vinyl tiles.', "Solvent-free pressure-sensitive adhesive for luxury vinyl tiles and planks on absorbent subfloors.", 'tub', '15 litres', 7200, 1, 1, 0, 'tub:#e7e2d4', 0, [6 => 6600, 18 => 6100]],
        ['adhesives-prep', 'RZ-UND-AC3', 'Acoustic underlay 3mm', 'Underlay for laminate and wood floors.', "3mm acoustic underlay with damp-proof film for floating laminate and engineered floors.", 'roll', '15 m² per roll', 2400, 2, 1, 0, 'roll:#5f7a6c', 0, [10 => 2150, 40 => 1940]],
        ['sundries', 'RZ-DST-P10', 'Polythene dust sheets 4×5m, pack of 10', 'Medium-gauge polythene sheets.', "Disposable polythene dust sheets for protecting floors and furniture.", 'pack', '10 sheets', 690, 2, 1, 0, 'roll:#dfe6e8', 0, [10 => 590]],
        ['sundries', 'RZ-MSK-25C', 'Multi-surface masking tape 25mm, case of 24', 'Clean release for up to 14 days.', "Multi-surface masking tape, 25mm × 50m rolls, 24 rolls per case.", 'case', '24 rolls', 3400, 1, 1, 0, 'roll:#f2c230', 0, [5 => 3050, 20 => 2780]],
        ['sundries', 'RZ-RLR-9M10', 'Medium-pile roller sleeves 9", box of 10', 'For emulsion on smooth and semi-rough walls.', "9 inch medium-pile woven roller sleeves for emulsion.", 'box', '10 sleeves', 2200, 1, 1, 0, 'roll:#3d5a73', 0, [5 => 1980, 20 => 1790]],
    ];

    foreach ($p as [$cat, $sku, $name, $short, $desc, $unit, $pack, $price, $moq, $step, $pallet, $swatch, $featured, $tiers]) {
        $pid = insert('products', [
            'category_id' => $catIds[$cat], 'sku' => $sku, 'slug' => slugify($name), 'name' => $name,
            'short' => $short, 'description' => $desc, 'unit' => $unit, 'pack_note' => $pack,
            'price_pence' => $price, 'moq' => $moq, 'step' => $step, 'pallet_qty' => $pallet,
            'stock' => 500, 'swatch' => $swatch, 'featured' => $featured, 'active' => 1, 'created_at' => now(),
        ]);
        foreach ($tiers as $min => $tp) {
            insert('price_tiers', ['product_id' => $pid, 'min_qty' => $min, 'price_pence' => $tp]);
        }
    }

    ensure_admin();
}

function ensure_admin(): void
{
    if ((int) val('SELECT COUNT(*) FROM admins') === 0) {
        insert('admins', [
            'email' => strtolower((string) config('app.admin_email')),
            'password_hash' => password_hash((string) config('app.admin_password'), PASSWORD_DEFAULT),
            'name' => 'Administrator',
            'created_at' => now(),
        ]);
    }
}
