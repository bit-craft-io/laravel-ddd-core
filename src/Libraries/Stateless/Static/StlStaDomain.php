<?php

declare(strict_types=1);

namespace BitCraft\Core\Libraries\Stateless\Static;

use BitCraft\Core\Domains\Entity\BaseEnt;
use BitCraft\Core\Libraries\Stateful\Instance\StfInsIterator;
use BitCraft\Core\Libraries\Stateful\Static\StfStaFactory;
use Illuminate\Database\Eloquent\Collection;

final class StlStaDomain
{
    /**
     * @template T of object
     * @param class-string<T> $repository_class
     * @return T
     */
    public static function rep(string $repository_class)
    {
        return StfStaFactory::singleton($repository_class);
    }

    /**
     * @template T of object
     * @param class-string<T> $entity_class
     * @return T
     */
    public static function ent(string $entity_class)
    {
        return StfStaFactory::prototype($entity_class);
    }

    /**
     * @template T
     * @param string $entity_class
     * @param Collection $collection
     * @param string $key
     * @return StfInsIterator<T>
     */
    public static function entIterator(string $entity_class, Collection $collection, string $key = 'id'): StfInsIterator
    {
        /** @var BaseEnt $ent */
        $ent = StfStaFactory::prototype($entity_class);
        return $ent->iterator($collection, $key);
    }

    /**
     * @template T of object
     * @param class-string<T> $vo_class
     * @return T
     */
    public static function vo(string $vo_class)
    {
        return StfStaFactory::prototype($vo_class);
    }

//    /**
//     * @template T
//     * @param string $vo_class
//     * @param Collection $collection
//     * @param string $key
//     * @return StfInsIterator<T>
//     */
//    public static function voIterator(string $vo_class, Collection $collection, string $key = 'id'): StfInsIterator
//    {
//        /** @var BaseVo $vo */
//        $vo = StfStaFactory::prototype($vo_class);
//        return $vo->iterator($collection, $key);
//    }

    /**
     * @template T of object
     * @param class-string<T> $vp_class
     * @return T
     */
    public static function vp(string $vp_class)
    {
        return StfStaFactory::singleton($vp_class);
    }

    /**
     * @template T of object
     * @param class-string<T> $vo_class
     * @return T
     */
    public static function mstVo(string $vo_class)
    {
        return StfStaFactory::singleton($vo_class);
    }

    /**
     * @template T of object
     * @param class-string<T> $svc_class
     * @return T
     */
    public static function svc(string $svc_class)
    {
        return StfStaFactory::prototype($svc_class);
    }
}
