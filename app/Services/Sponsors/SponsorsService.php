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
            ->where('md_sponsor.type3', $type)
            ->where('md_sponsor.status3', 'show')
            ->orderby('md_sponsor.sort3', 'asc')
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
        $sponsor = DB::table('events_sponsors_temporary as est')
            ->join('md_landyark as ml', 'ml.id', '=', 'est.md_sponsor_id')
            ->select('ml.id', 'ml.name', 'ml.image', 'ml.link', 'ml.desc')
            ->where('est.events_id', 14)
            ->where('est.type', 'landyard')
            ->orderBy('ml.sort', 'asc')
            ->get();

        foreach ($sponsor as $row) {
            // Jika field slug diperlukan, pastikan sudah di-select (misal: 'ml.slug')
            $row->name  = 'Landyark Sponsors';
            $row->type  = (!empty($row->slug) ? 'premium' : 'free');
            $row->image = (!empty($row->image) ? $row->image : '');
        }
        return $sponsor;
    }

    public function getMobile()
    {
        $sponsor = DB::table('events_sponsors_temporary as est')
            ->join('md_mobile as ml', 'ml.id', '=', 'est.md_sponsor_id')
            ->select('ml.id', 'ml.name', 'ml.image', 'ml.link', 'ml.desc')
            ->where('est.events_id', 14)
            ->where('est.type', 'mobile')
            ->orderBy('ml.sort', 'asc')
            ->get();

        foreach ($sponsor as $row) {
            // Jika field slug diperlukan, pastikan sudah di-select (misal: 'ml.slug')
            $row->name  = 'IM Mobile App Sponsors';
            $row->type  = (!empty($row->slug) ? 'premium' : 'free');
            $row->image = (!empty($row->image) ? $row->image : '');
        }
        return $sponsor;
    }


    public function getCharging()
    {
        $sponsor = DB::table('events_sponsors_temporary as est')
            ->join('md_charging as mc', 'mc.id', '=', 'est.md_sponsor_id')
            ->select('mc.id', 'mc.name', 'mc.image', 'mc.link', 'mc.desc')
            ->where('est.events_id', 14)
            ->where('est.type', 'charging')
            ->orderBy('mc.sort', 'asc')
            ->get();

        foreach ($sponsor as $row) {
            $row->name  = 'Charging Station Sponsors';
            $row->type  = (!empty($row->slug) ? 'premium' : 'free');
            $row->image = (!empty($row->image) ? $row->image : '');
        }
        return $sponsor;
    }

    public function getKnowledge()
    {
        $sponsor = DB::table('events_sponsors_temporary as est')
            ->join('md_knowledge_partner as mkp', 'mkp.id', '=', 'est.md_sponsor_id')
            ->select('mkp.id', 'mkp.name', 'mkp.image', 'mkp.link', 'mkp.desc')
            ->where('est.events_id', 14)
            ->where('est.type', 'knowledge')
            ->orderBy('mkp.sort', 'asc')
            ->get();

        foreach ($sponsor as $row) {
            $row->name  = 'Knowledge Partner';
            $row->type  = (!empty($row->slug) ? 'premium' : 'free');
            $row->image = (!empty($row->image) ? $row->image : '');
        }
        return $sponsor;
    }

    public function getRegistration()
    {
        $sponsor = DB::table('events_sponsors_temporary as est')
            ->join('md_registration as mr', 'mr.id', '=', 'est.md_sponsor_id')
            ->select('mr.id', 'mr.name', 'mr.image', 'mr.link', 'mr.desc')
            ->where('est.events_id', 14)
            ->where('est.type', 'registration')
            ->orderBy('mr.sort', 'asc')
            ->get();

        foreach ($sponsor as $row) {
            $row->name  = 'Registration Sponsors';
            $row->type  = (!empty($row->slug) ? 'premium' : 'free');
            $row->image = (!empty($row->image) ? $row->image : '');
        }
        return $sponsor;
    }

    public function getLunch()
    {
        $sponsor = DB::table('events_sponsors_temporary as est')
            ->join('md_lunch as ml', 'ml.id', '=', 'est.md_sponsor_id')
            ->select('ml.id', 'ml.name', 'ml.image', 'ml.link', 'ml.desc')
            ->where('est.events_id', 14)
            ->where('est.type', 'lunch')
            ->orderBy('ml.sort', 'asc')
            ->get();

        foreach ($sponsor as $row) {
            $row->name  = 'Lunch Sponsors';
            $row->type  = (!empty($row->slug) ? 'premium' : 'free');
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
            ->where('status3', 'show')
            ->orderBy('md_medical.sort3', 'asc')
            ->get();
        foreach ($sponsor as $x => $row) {
            $row->name = 'Medical PARTNERS';
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
            ->where('status3', 'show')
            ->orderBy('md_association.sort3', 'asc')
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
