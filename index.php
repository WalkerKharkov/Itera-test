<?php

define( "WIDTH", 3 );
define( "HEIGHT", 3 );

$data = array(
    array( 'text' => 'Текст красного цвета',
        'cells' => '1,2,3',
        'align' => 'center',
        'valign' => 'center',
        'color' => 'FF0000',
        'bgcolor' => '0000FF'),
    array( 'text' => 'Текст зеленого цвета',
        'cells' => '4, 7',
        'align' => 'right',
        'valign' => 'bottom',
        'color' => '00FF00',
        'bgcolor' => 'FFFFFF'),
);

function is_match( $data ){
    $cells = array();
    foreach ( $data as $block ){
        $cells_list = explode( ',', $block[ "cells" ] );
        foreach ( $cells_list as $cell_number ){
            array_push( $cells, $cell_number );
        }
    }
    $match = array_count_values( $cells );
    foreach ( $match as $value ){
        if ( $value > 1 ){
            return true;
        }
    }
    return false;
}

function get_cell_width( $cells ){
    $width = 1;
    $length = count( $cells );
    for ( $i = 0; $i < $length - 1; $i++ ){
        if ( ( $cells[ $i + 1 ] - $cells[ $i ] ) === 1 ){
            $width++;
            if ( $width >= WIDTH ){
                $width = WIDTH;
                return $width;
            }
        }
        else {
            return $width;
        }
    }
    return $width;
}

function get_cell_height( $cells ){
    $height = 0;
    $match = 1;
    $cell = $cells[ 0 ] + HEIGHT;
    while ( $match > 0 ){
        $match = array_search( $cell, $cells );
        $height++;
        $cell += HEIGHT;
    }
    return $height;
}

function get_modified_cells( $data ){
    $modified_cells = array();
    $count = 0;
    foreach ( $data as $block ){
        $modified_cells[ $count ][ "firstcell" ] = substr( $block[ "cells" ], 0, strpos( $block[ "cells" ], ',' ) );
        $cells = explode( ',', $block[ "cells" ] );
        $modified_cells[ $count ][ "cellwidth" ] = get_cell_width( $cells );
        $modified_cells[ $count ][ "cellheight" ] = get_cell_height( $cells );
        $modified_cells[ $count ][ "text" ] = $block[ "text" ];
        $modified_cells[ $count ][ "align" ] = $block[ "align" ];
        $modified_cells[ $count ][ "valign" ] = $block[ "valign" ];
        $modified_cells[ $count ][ "color" ] = $block[ "color" ];
        $modified_cells[ $count ][ "bgcolor" ] = $block[ "bgcolor" ];
        $count++;
    }
    return $modified_cells;
}

function get_cell_map( $modified_cells ){
    $cell_map = array();
    $size = WIDTH * HEIGHT;
    for ( $i = 1; $i <= $size; $i++ ){
        $cell_map[ $i ] = $i;
    }

    foreach( $modified_cells as $cell ){
        $cell_num = $cell[ "firstcell" ];
        for ( $i = 0; $i < $cell[ "cellwidth" ]; $i++ ){
            for ( $j = 0; $j < $cell[ "cellheight" ]; $j++ ){
                $index = $i + $j * WIDTH + $cell_num;
                if ( $cell_num != $index ){
                    $cell_map[ $index ] = '';
                }
                else{
                    $cell_map[ $index ] .= 'm';
                }
            }
        }
    }
    return $cell_map;
}

function table_render( $data ){
    $wrong = "Wrong input data!";
    if ( ! is_array( $data ) || is_match( $data ) ) {
        return $wrong;
    }
    $page = "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n<meta charset=\"UTF-8\">\n<title>Itera test</title>\n</head>\n<body>\n" .
            "<table border=\"1\" cellpadding=\"4\" cellspacing=\"0\" >\n<tr>\n";
    $modified_cells = get_modified_cells( $data );
    $cell_map = get_cell_map( $modified_cells );
    $cell_width = 100;
    $cell_height = 100;
    $count = count( $cell_map );
    for ( $i = 1; $i <= $count; $i++ ){
        $modified = strpos( $cell_map[ $i ], 'm' );
        if ( $modified ){
            $cell_number = substr( $cell_map[ $i ], 0, strlen( $cell_map[ $i ] ) - 1 );
            foreach( $modified_cells as $modified_cell ){
                if ( $cell_number == $modified_cell[ "firstcell" ] ){
                    $page .= "<td rowspan=\"" . $modified_cell[ "cellheight" ] . "\" colspan=\"" . $modified_cell[ "cellwidth" ] .
                        "\" style=\"color:#" . $modified_cell[ "color" ] . ";background-color:#" . $modified_cell[ "bgcolor" ] .
                        ";text-align:" . $modified_cell[ "align" ] . ";vertical-align:" . $modified_cell[ "valign" ] .
                        ";width:" . $cell_width * $modified_cell[ "cellwidth" ] . "px;height:" . $cell_height * $modified_cell[ "cellheight"] .
                        "px;\">" . $modified_cell[ "text" ] . "</td>\n";
                }
            }
        }
        elseif( $cell_map[ $i ] ){
            $page .= "<td style=\"width:" . $cell_width . "px;height:" . $cell_height . "px;\">" . $i . "</td>\n";
        }
        if ( 0 === $i % WIDTH ){
            if ( $i === $count ){
                $page .= "</tr>\n";
            }
            else{
                $page .= "</tr><tr>\n";
            }
        }
    }
    $page .= "</table>\n</body>\n</html>\n";
    return $page;
}

echo table_render( $data );
