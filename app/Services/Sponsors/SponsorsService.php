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
            )->join('events_sponsors_temporary', 'events_sponsors_temporary.md_sponsor_id', '=', 'md_landyark.id')
            ->where('events_sponsors_temporary.events_id', '=', '13')
            ->orderBy('md_landyark.sort', 'asc')
            ->get();
        foreach ($sponsor as $x => $row) {
            $row->name = 'Landyard Sponsors';
            $row->type = (!empty($row->slug) ? 'premium' : 'free');
            $row->image = (!empty($row->image) ? $row->image : '');
        }

        return $sponsor;
    }
    public function getCharging()
    {
        $sponsor =  DB::table('md_charging')
            ->select(
                'md_charging.id',
                'md_charging.image',
                'md_charging.link'
            )->join('events_sponsors_temporary', 'events_sponsors_temporary.md_sponsor_id', '=', 'md_charging.id')
            ->where('events_sponsors_temporary.events_id', '=', '13')
            ->orderBy('md_charging.sort', 'asc')
            ->get();
        foreach ($sponsor as $x => $row) {
            $row->name = 'Charging Station Sponsors';
            $row->type = (!empty($row->slug) ? 'premium' : 'free');
            $row->image = (!empty($row->image) ? $row->image : '');
        }

        return $sponsor;
    }
    public function getKnowledge()
    {
        $sponsor =  DB::table('md_knowledge_partner')
            ->select(
                'md_knowledge_partner.id',
                'md_knowledge_partner.image',
                'md_knowledge_partner.link'
            )->join('events_sponsors_temporary', 'events_sponsors_temporary.md_sponsor_id', '=', 'md_knowledge_partner.id')
            ->where('events_sponsors_temporary.events_id', '=', '13')
            ->orderBy('md_knowledge_partner.sort', 'asc')
            ->get();
        foreach ($sponsor as $x => $row) {
            $row->name = 'Knowledge Partner';
            $row->type = (!empty($row->slug) ? 'premium' : 'free');
            $row->image = (!empty($row->image) ? $row->image : '');
        }

        return $sponsor;
    }
    public function getRegistration()
    {
        $sponsor =  DB::table('md_registration')
            ->select(
                'md_registration.id',
                'md_registration.image',
                'md_registration.link'
            )->join('events_sponsors_temporary', 'events_sponsors_temporary.md_sponsor_id', '=', 'md_registration.id')
            ->where('events_sponsors_temporary.events_id', '=', '13')
            ->orderBy('md_registration.sort', 'asc')
            ->get();
        foreach ($sponsor as $x => $row) {
            $row->name = 'Registration Sponsors';
            $row->type = (!empty($row->slug) ? 'premium' : 'free');
            $row->image = (!empty($row->image) ? $row->image : '');
        }

        return $sponsor;
    }
    public function getLunch()
    {
        $sponsor =  DB::table('md_lunch')
            ->select(
                'md_lunch.id',
                'md_lunch.image',
                'md_lunch.link'
            )->join('events_sponsors_temporary', 'events_sponsors_temporary.md_sponsor_id', '=', 'md_lunch.id')
            ->where('events_sponsors_temporary.events_id', '=', '13')
            ->orderBy('md_lunch.sort', 'asc')
            ->get();
        foreach ($sponsor as $x => $row) {
            $row->name = 'Registration Sponsors';
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

    public function getMedical()
    {
        $sponsor = DB::table('md_medical')
            ->select(
                'md_medical.id',
                'md_medical.image',
                'md_medical.link'
            )
            ->where('status', 'show')
            ->orderBy('md_medical.sort', 'asc')
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
        } elseif ($type == 'registration') {
            $find = DB::table('md_registration')->where('id', '=', $id)->first();
        } elseif ($type == 'lunch') {
            $find = DB::table('md_lunch')->where('id', '=', $id)->first();
        } elseif ($type == 'charging') {
            $find = DB::table('md_charging')->where('id', '=', $id)->first();
        } elseif ($type == 'knowledge') {
            $find = DB::table('md_knowledge_partner')->where('id', '=', $id)->first();
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
