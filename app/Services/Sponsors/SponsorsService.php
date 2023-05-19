<?php

namespace App\Services\Sponsors;

use App\Repositories\SponsorRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

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
            ->where('md_sponsor.status', 'show')
            ->orderby('md_sponsor.sort', 'asc')
            ->get();

        foreach ($sponsor as $x => $row) {
            $row->name = $row->type . ' Sponsors';
            $row->type = (!empty($row->slug) ? 'premium' : 'free');
            $row->image = (!empty($row->image) ? $row->image : '');
        }
        return $sponsor;
    }

    public function getLandyark()
    {
        $sponsor =  DB::table('md_landyark')
            ->select(
                'md_landyark.id',
                'md_landyark.image',
                'md_landyark.link'
            )
            ->where('status', 'show')
            ->orderBy('md_landyark.sort', 'asc')
            ->get();
        foreach ($sponsor as $x => $row) {
            $row->name = 'Landyard Sponsors';
            $row->type = (!empty($row->slug) ? 'premium' : 'free');
            $row->image = (!empty($row->image) ? $row->image : '');
        }

        return $sponsor;
    }

    public function getMedia()
    {
        $sponsor = DB::table('md_media_partner')
            ->select(
                'md_media_partner.id',
                'md_media_partner.image',
                'md_media_partner.link'
            )
            ->where('status', 'show')
            ->orderBy('md_media_partner.sort', 'asc')
            ->get();
        foreach ($sponsor as $x => $row) {
            $row->name = 'Media PARTNERS';
            $row->type = (!empty($row->slug) ? 'premium' : 'free');
            $row->image = (!empty($row->image) ? $row->image : '');
        }

        return $sponsor;
    }

    public function getSupporting()
    {
        $sponsor = DB::table('md_association')
            ->select(
                'md_association.id',
                'md_association.image',
                'md_association.link'
            )
            ->where('status', 'show')
            ->orderBy('md_association.sort', 'asc')
            ->get();
        foreach ($sponsor as $x => $row) {
            $row->name = 'Media PARTNERS';
            $row->type = (!empty($row->slug) ? 'premium' : 'free');
            $row->image = (!empty($row->image) ? $row->image : '');
        }

        return $sponsor;
    }


    public function getDetailSponsorFree($id, $type)
    {
        if ($type == 'Sponsor') {
            $find = DB::table('md_sponsor')->where('id', '=', $id)->first();
        } elseif ($type == 'Media Partner') {
            $find = DB::table('md_media_partner')->where('id', '=', $id)->first();
        } elseif ($type == 'Association') {
            $find = DB::table('md_association')->where('id', '=', $id)->first();
        } elseif ($type == 'Landyard') {
            $find = DB::table('md_landyark')->where('id', '=', $id)->first();
        } elseif ($type == 'Badges') {
            $find = DB::table('md_landyark')->where('id', '=', $id)->first();
        } else {
            $find = 'Type Not Found';
        }
        return $find;
    }

    public function getDetailSponsorPremium($slug)
    {
        //
    }
}
