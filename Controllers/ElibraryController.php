<?php


namespace App\Http\Controllers;

use Anomaly\Streams\Platform\Image\Image;
use Anomaly\Streams\Platform\Http\Controller\PublicController;
use Anomaly\Streams\Platform\Model\ELibrary\ELibraryMainPageEntryModel;
use Anomaly\Streams\Platform\Model\ELibrary\ELibraryCategoriesEntryModel;
use Anomaly\Streams\Platform\Model\ELibrary\ELibraryDocumentsEntryModel;
use Anomaly\Streams\Platform\Model\ELibrary\ELibraryVideosEntryModel;
use Anomaly\VideoFieldType\Matcher\Command\GetMatcher;


class ElibraryController extends PublicController{

    public function libContent(){

        $mainPage = ELibraryMainPageEntryModel::query()->first();

        $eCategory = ELibraryCategoriesEntryModel::query()
            ->where('status', true)
            ->orderBy('sort_order','desc')
            ->get();

            //dd($eCategory->toArray());

        $firstCate = ELibraryCategoriesEntryModel::query()
            ->orderBy('sort_order','desc')
            ->firstOrfail();



        $eDocument = ELibraryDocumentsEntryModel::query()
            ->where('status', true)
            ->get();

            //dd($eDocument->toArray());

        $eVideo = ELibraryVideosEntryModel::query()
            ->where('status', true)
            ->get();

        //dd($eVideo->toArray());

        return view('e-library', [

            'mainPage'=>$mainPage,
            'firstCate'=>$firstCate,
           
            'eCategory'=>$eCategory,
            'eDocument'=>$eDocument,
            'eVideo'=>$eVideo,

        ]);
    }

    public function eDoc(Image $image, $category){

        $dataDocument = ELibraryDocumentsEntryModel::query()
            ->where('select_categories_id', $category )
            ->where('status', true)
            ->orderBy('sort_order','desc')
            ->paginate(8)
            ->through(function ($data) use ($image){
                return [
                    'id' => $data['id'],
                    'title' => $data['title'],
                    'short_description' => $data['short_description'],
                    'image' => $data['document_thumb_image'] ? $image->make($data['document_thumb_image'])->fit(296, 210)->output() : "",
                    'pdf_file' => url()->to('files/'.urlencode($data['pdf_file']->path())),


                ];
            });

        //dd($dataDocument->toArray());

        return response()->json([
            'data' => $dataDocument
        ]);

    }

    public function eDocSort(Image $image, $category, $sorting){
        if ($sorting == 'newest') {
            $dataDocument = ELibraryDocumentsEntryModel::query()
                ->where('select_categories_id', $category )
                ->orderBy('sort_order','desc')
                ->paginate(8)
                ->through(function ($data) use ($image){
                    return [
                        'id' => $data['id'],
                        'title' => $data['title'],
                        'short_description' => $data['short_description'],
                        'image' => $data['document_thumb_image'] ? $image->make($data['document_thumb_image'])->fit(250, 250)->output() : "",
                        'pdf_file' => url()->to('files/'.urlencode($data['pdf_file']->path())),


                    ];
                });
        } else {
            $dataDocument = ELibraryDocumentsEntryModel::query()
                ->where('select_categories_id', $category )
                ->orderBy('id')
                ->paginate(8)
                ->through(function ($data) use ($image){
                    return [
                        'id' => $data['id'],
                        'title' => $data['title'],
                        'short_description' => $data['short_description'],
                        'image' => $data['document_thumb_image'] ? $image->make($data['document_thumb_image'])->fit(250, 250)->output() : "",
                        'pdf_file' => url()->to('files/'.urlencode($data['pdf_file']->path())),


                    ];
                });
        }


        //dd($dataDocument->toArray());

        return response()->json([
            'data' => $dataDocument
        ]);

    }

    public function eVid(Image $image, $vid){

        $dataDocument = ELibraryVideosEntryModel::query()
            ->where('select_categories_id', $vid )
            ->orderBy('sort_order','desc')
            ->get()
            ->transform(function ($data, $key) use ($image){

                return [
                    'id' => $data['id'],
                    'title' => $data['title'],
                    'short_description' => $data['short_description'],
                    'image' => ($key === 0) ? $image->make($data['video_thumb_image'])->fit(1195, 577)->output() : $image->make($data['video_thumb_image'])->fit(250, 250)->output() ,
                    'video' => video_embed($data['video_url']),


                ];
            });


        return response()->json([
            'data' => $dataDocument
        ]);

    }

    public function eVidSort(Image $image, $vid, $sorting){

        if ($sorting == 'newest') {
            $dataDocument = ELibraryVideosEntryModel::query()
                ->where('select_categories_id', $vid )
                ->orderByDesc('id')
                ->get()
                ->transform(function ($data, $key) use ($image){

                    return [
                        'id' => $data['id'],
                        'title' => $data['title'],
                        'short_description' => $data['short_description'],
                        'image' => ($key === 0) ? $image->make($data['video_thumb_image'])->fit(1195, 577)->output() : $image->make($data['video_thumb_image'])->fit(250, 250)->output() ,
                        'video' => video_embed($data['video_url']),


                    ];
                });
        } else {
            $dataDocument = ELibraryVideosEntryModel::query()
                ->where('select_categories_id', $vid )
                ->orderBy('id')
                ->get()
                ->transform(function ($data, $key) use ($image){

                    return [
                        'id' => $data['id'],
                        'title' => $data['title'],
                        'short_description' => $data['short_description'],
                        'image' => ($key === 0) ? $image->make($data['video_thumb_image'])->fit(1195, 577)->output() : $image->make($data['video_thumb_image'])->fit(250, 250)->output() ,
                        'video' => video_embed($data['video_url']),


                    ];
                });
        }


        return response()->json([
            'data' => $dataDocument
        ]);

    }

    // public function sort($category, $vid){

    //     $dataDocument = ELibraryDocumentsEntryModel::query()
    //         ->where('select_categories_id', $category)
    //         ->get();

    //     $dataVid = ELibraryVideosEntryModel::query()
    //         ->where('select_categories_id', $vid)
    //         ->get();

    //     $dataPdf = collect($dataDocument->toArray());

    //     $newest = $dataPdf->sort();
    //     $oldest = $dataPdf->sortDesc();

    //     $dataVideo = collect($dataVid->toArray());

    //     dd($newest->toArray());

    //     return response()->json([

    //     ]);
    // }

}
