<?php

namespace App\Services\Company;

use App\Repositories\CompanyRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CompanyService implements CompanyRepositoryInterface
{
    public function getCompanyById($id)
    {
        //
    }

    public function getCompanyBySlug($slug)
    {
        return DB::table('company')->where('slug', '=', $slug)->first();
    }

    public function getListContactById($id)
    {
        return DB::table('company_representative')
            ->select(
                'company_representative.id',
                'company_representative.name',
                'company_representative.position',
                'company_representative.image',
                'company_representative.bio as biografi',
                'company_representative.linkedin',
                'company_representative.facebook',
                'company_representative.twitter'
            )
            ->where(function ($q) use ($id) {
                if ($id) {
                    $q->where('company_representative.company_id', $id);
                }
            })
            ->orderby('company_representative.id')
            ->get();
    }
    public function getDetailContactById($id)
    {
        return DB::table('company_representative')
            ->select(
                'company_representative.id',
                'company_representative.name',
                'company_representative.position',
                'company_representative.image',
                'company_representative.bio as biografi',
                'company_representative.linkedin',
                'company_representative.facebook',
                'company_representative.twitter'
            )
            ->where(function ($q) use ($id) {
                if ($id) {
                    $q->where('company_representative.id', $id);
                }
            })
            ->first();
    }

    public function getListTimeline($type, $company_id)
    {

        if ($type == 'Product') {

            dd("Prduk");
        } elseif ($type == 'Media') {
            // self::usersActivity('Visit Directory Media Resource', (self::isInEvents()?'In':'Out').' Events', self::getIdEvents());

            // $res['item'] = MediaResourceService::paginateWithFilter($search, $tags, $category, $filter);
            dd("media");
        } elseif ($type == 'Project') {
            // self::usersActivity('Visit Directory Project', (self::isInEvents()?'In':'Out').' Events', self::getIdEvents());

            // $res['item'] = ProjectService::paginateWithFilter($search, $tags, $category, $filter);
            dd("Project");
        } elseif ($type == 'News') {
            // self::usersActivity('Visit Directory News', (self::isInEvents()?'In':'Out').' Events', self::getIdEvents());

            // $res['item'] = NewsService::paginateWithFilter($search, $tags, $category, $filter);
            dd("News");
        } elseif ($type == 'Videos') {
            //
            dd("Videos");
        }
    }
}
