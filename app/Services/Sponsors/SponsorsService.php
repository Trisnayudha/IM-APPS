<?php

namespace App\Services\Sponsors;

use App\Repositories\SponsorRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SponsorsService implements SponsorRepositoryInterface
{
    public function getSponsorsType($type)
    {
        $sponsor = DB::table('md_sponsor')
            ->select(
                'md_sponsor.id',
                'md_sponsor.image',
                'md_sponsor.type',
                'md_sponsor.slug'

            )
            ->where('md_sponsor.type', $type)
            ->orderby('md_sponsor.sort', 'asc')
            ->get();

        foreach ($sponsor as $x => $row) {
            $row->name = $row->type . ' Sponsors';
            $row->image = (!empty($row->image) ? $row->image : '');
        }
        return $sponsor;
    }

    public function getLandyark()
    {
        return DB::table('md_landyark')
            ->select(
                'md_landyark.id',
                'md_landyark.image',
                'md_landyark.link'
            )
            ->orderBy('md_landyark.sort', 'asc')
            ->get();
    }

    public function getMedia()
    {
        return DB::table('md_media_partner')
            ->select(
                'md_media_partner.id',
                'md_media_partner.image',
                'md_media_partner.link'
            )
            ->orderBy('md_media_partner.sort', 'asc')
            ->get();
    }

    public function getSupporting()
    {
        return DB::table('md_association')
            ->select(
                'md_association.id',
                'md_association.image',
                'md_association.link'
            )
            ->orderBy('md_association.sort', 'asc')
            ->get();
    }
}
