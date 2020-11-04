<?php

/**
 * Helper function to add a resource to the push stack
 *
 * @param string   $resource
 * @param int|null $expires
 */
function h2push($resource, $expires = null)
{
    resolve('http2push')->add($resource, $expires);

    return $resource;
}
