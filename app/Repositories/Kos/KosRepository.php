<?php

namespace App\Repositories\Kos;

use App\Interfaces\Kos\KosInterface;
use App\Traits\{BugsnagTrait, ResponseBuilder, FileManagerTrait};
use App\Models\User\UserCredit;
use App\Models\Kos\{
    Kos,
    Facility,
    Room,
    KosImage,
    Address,
};
use App\Http\Resources\Kos\{
    KosResource,
    KosCollection,
    KosDetailResource,
    RoomResource,
};
use Throwable;
use DB;
use Auth;

class KosRepository implements KosInterface
{
    use BugsnagTrait, ResponseBuilder, FileManagerTrait;

    const PER_PAGE = 20;
    const CURRENT_PAGE = 1;

    public function index($request)
    {
        try {
            $perPage = $request->perPage?:self::PER_PAGE;
            $currentPage = $request->currentPage?:self::CURRENT_PAGE;

            $query = Kos::query();

            if ($request->filled('q')) {
                $q = strip_tags(trim($request->q));
                $query->where(function ($query) use($q) {
                    $query->where(DB::raw('UPPER(name)'), 'like', '%' . strtoupper($q) . '%')->
                            orWhere('price', 'like', '%' . $q . '%');
                  });
            }

            if($request->filled('sort_by')) {
                $sort_type = $request->sort_type ?? 'ASC';
                if($request->sort_by == 'price')
                $query->orderBy('price', $sort_type);
            } else {
                $query->latest();
            }

            if($request->filled('location')) {
                $query->whereHas('address', function($query) use ($request) {
                    $query->where(DB::raw('UPPER(province)'), 'like', '%' . strtoupper($request->location) . '%')
                            ->orWhere(DB::raw('UPPER(city)'), 'like', '%' . strtoupper($request->location) . '%')
                            ->orWhere(DB::raw('UPPER(district)'), 'like', '%' . strtoupper($request->location) . '%')
                            ->orWhere(DB::raw('UPPER(address)'), 'like', '%' . strtoupper($request->location) . '%');
                });
            }

            $query->where('user_id', auth()->user()->id);

            $data = new KosCollection($query->paginate($perPage, ['*'], 'page', $currentPage));

            return $this->sendResponse($data);
        } catch (Throwable $e) {
            $this->report($e);
            return $this->sendError(400, "Whoops something wrong with #index");
        }
    }

    public function show($id)
    {
        try {
            $kos = Kos::with(['kosImage', 'room', 'facility', 'address'])
                        ->where('id', dekrip($id))->where('user_id', auth()->user()->id)->first();
            if(!$kos)
                return $this->sendError(404, 'Kos not found');

            return $this->sendResponse(new KosDetailResource($kos));
        } catch (Throwable $e) {
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #show');
        }
    }

    public function store($request)
    {
        DB::beginTransaction();

        try {
            $item = array(
                        'name' => $request->name,
                        'price' => $request->price,
                        'kos_type' => $request->kos_type,
                        'description' => $request->description,
                        'kos_established' => $request->kos_established,
                        'room_type' => $request->room_type,
                        'admin_name' => $request->admin_name,
                        'user_id' => auth()->user()->id,
                    );

            $data = Kos::create($item);

            if($data) {
                if($request->filled('facility') && count($request->facility) > 0) {
                    Facility::create([
                        'kos_id' => $data->id,
                        'public_facility' => $request->facility['public_facility'],
                        'room_facility' => $request->facility['room_facility'],
                        'bath_facility' => $request->facility['bath_facility'],
                        'park_facility' => $request->facility['park_facility'],
                    ]);
                }

                if($request->filled('kos_image') && count($request->kos_image) > 0) {
                    foreach($request->kos_image as $row) {
                        //upload file
                        $filename = $filename = $this->uploadFileBase64($row['image'], '/kos');

                        KosImage::create([
                            'kos_id' => $data->id,
                            'image' => $filename,
                            'type' => $row['type'],
                        ]);
                    }
                }

                if($request->filled('address') && count($request->address) > 0) {
                    Address::create([
                        'kos_id' => $data->id,
                        'province' => $request->address['province'],
                        'city' => $request->address['city'],
                        'district' => $request->address['district'],
                        'address' => $request->address['address'],
                    ]);
                }

                if($request->filled('room') && count($request->room) > 0) {
                    Room::create([
                        'kos_id' => $data->id,
                        'size' => $request->room['size'],
                        'total_room' => $request->room['total_room'],
                        'available_room' => $request->room['available_room'],
                    ]);
                }
            }

            DB::commit();
            return $this->sendResponse(null, 'Tambah data berhasil', 201);
        } catch (Throwable $e) {
            DB::rollBack();
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #store');
        }
    }

    public function update($id, $request)
    {
        DB::beginTransaction();

        try {
            $id = dekrip($id);
            $kos = Kos::where('id', $id)->where('user_id', auth()->user()->id)->first();

            $item = array(
                'name' => $request->name ?? $kos->name,
                'price' => $request->price ?? $kos->price,
                'kos_type' => $request->kos_type ?? $kos->kos_type,
                'description' => $request->description ?? $kos->description,
                'kos_established' => $request->kos_established ?? $kos->kos_established,
                'room_type' => $request->room_type ?? $kos->room_type,
                'admin_name' => $request->admin_name ?? $kos->admin_name,
            );

            $data = $kos->update($item);

            if($data) {
                if($request->filled('facility') && count($request->facility) > 0) {
                    $dataFacility = Facility::where('kos_id', $id)->first();

                    if($dataFacility)
                        $idFacility = $dataFacility->id;
                    else
                        $idFacility = 0;

                    $facility = array(
                        'kos_id' => $id,
                        'public_facility' => $request->facility['public_facility'],
                        'room_facility' => $request->facility['room_facility'],
                        'bath_facility' => $request->facility['bath_facility'],
                        'park_facility' => $request->facility['park_facility'],
                    );

                    Facility::updateOrCreate(['id' => $idFacility], $facility);
                }

                if($request->filled('kos_image') && count($request->kos_image) > 0) {
                    $imageId = [];
                    foreach($request->kos_image as $row) {
                        $kosImage = KosImage::where('kos_id', $id)->where('type', $row['type'])->first();

                        if($kosImage) {
                            $kosImageId = $kosImage->id;
                            $imageId[] = $kosImageId;

                            // delete image from disk
                            if ($kosImage->image) {
                                \Storage::disk('custom')->delete('kos/' . $kosImage->image);
                                \Storage::disk('custom')->delete('kos/thumbnail/' . $kosImage->image);
                            }
                        } else {
                            $kosImageId = 0;
                        }

                        //upload file
                        $filename = $filename = $this->uploadFileBase64($row['image'], '/kos');
                        KosImage::updateOrCreate(['id' => $kosImageId], ['kos_id' => $id,
                                                'image' => $filename,
                                                'type' => $row['type']]);
                    }

                    // delete image yang tidak sama dengan yang direquest
                    if(count($imageId) > 0) {
                        $deleteKosImage = KosImage::where('kos_id', $id)->whereNotIn('id', $imageId)->get();

                        foreach($deleteKosImage as $row) {
                            // delete image from disk
                            if ($row->image) {
                                \Storage::disk('custom')->delete('kos/' . $row->image);
                                \Storage::disk('custom')->delete('kos/thumbnail/' . $row->image);
                            }
                            $row->delete();
                        }
                    }
                }

                if($request->filled('address') && count($request->address) > 0) {
                    $dataAddress = Address::where('kos_id', $id)->first();

                    if($dataAddress)
                        $idAddress = $dataAddress->id;
                    else
                        $idAddress = 0;

                    $address = array(
                        'kos_id' => $id,
                        'province' => $request->address['province'],
                        'city' => $request->address['city'],
                        'district' => $request->address['district'],
                        'address' => $request->address['address'],
                    );

                    Address::updateOrCreate(['id' => $idAddress], $address);
                }

                if($request->filled('room') && count($request->room) > 0) {
                    $dataRoom = Room::where('kos_id', $id)->first();

                    if($dataRoom)
                        $idRoom = $dataRoom->id;
                    else
                        $idRoom = 0;

                    $room = array(
                        'kos_id' => $id,
                        'size' => $request->room['size'],
                        'total_room' => $request->room['total_room'],
                        'available_room' => $request->room['available_room'],
                    );

                    Room::updateOrCreate(['id' => $idRoom], $room);
                }
            }

            DB::commit();
            return $this->sendResponse(null, 'Update data berhasil');
        } catch (Throwable $e) {
            DB::rollBack();
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #update');
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $id = dekrip($id);
            $kos = Kos::where('id', $id)->where('user_id', auth()->user()->id)->first();
            if(!$kos)
                return $this->error(404, null, 'Kos not found');

            if($kos->delete()) {
                $kosImage = KosImage::where('kos_id', $id)->get();
                foreach($kosImage as $row) {
                    // delete image from disk
                    if ($row->image) {
                        \Storage::disk('custom')->delete('kos/' . $row->image);
                        \Storage::disk('custom')->delete('kos/thumbnail/' . $row->image);
                    }
                    $row->delete();
                }

                Facility::where('kos_id', $id)->delete();
                Address::where('kos_id', $id)->delete();
                Room::where('kos_id', $id)->delete();
            }

            DB::commit();
            return $this->sendResponse(null, 'Hapus data berhasil');
        } catch (Throwable $e) {
            DB::rollBack();
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #destroy');
        }
    }

    public function roomAvailibility($id)
    {
        DB::beginTransaction();
        try {
            $userCredit = UserCredit::where('user_id', auth()->user()->id)->first();

            if($userCredit && $userCredit->credit - $userCredit->credit_deduction > 0) {
                // cek ketersediaan kamar & bukan pemilik kos yang mengaksesnya
                $room = Room::where('kos_id', dekrip($id))->whereHas('kos', function($query) {
                            $query->where('user_id', '!=', auth()->user()->id);
                        })->first();
                if(!$room)
                    return $this->sendError(404, 'Room not found');

                $userCredit->credit_deduction += 5;
                $userCredit->save();

                DB::commit();
                return $this->sendResponse(new RoomResource($room));
            } else {
                return $this->sendError(400, 'Credit anda tidak mencukupi');
            }
        } catch (Throwable $e) {
            DB::rollback();
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #room_availibility');
        }
    }

    public function indexUser($request)
    {
        try {
            $perPage = $request->perPage?:self::PER_PAGE;
            $currentPage = $request->currentPage?:self::CURRENT_PAGE;

            $query = Kos::query();

            if ($request->filled('q')) {
                $q = strip_tags(trim($request->q));
                $query->where(function ($query) use($q) {
                    $query->where(DB::raw('UPPER(name)'), 'like', '%' . strtoupper($q) . '%')->
                            orWhere('price', 'like', '%' . $q . '%');
                  });
            }

            if($request->filled('sort_by')) {
                $sort_type = $request->sort_type ?? 'ASC';
                if($request->sort_by == 'price')
                $query->orderBy('price', $sort_type);
            } else {
                $query->latest();
            }

            if($request->filled('location')) {
                $query->whereHas('address', function($query) use ($request) {
                    $query->where(DB::raw('UPPER(province)'), 'like', '%' . strtoupper($request->location) . '%')
                            ->orWhere(DB::raw('UPPER(city)'), 'like', '%' . strtoupper($request->location) . '%')
                            ->orWhere(DB::raw('UPPER(district)'), 'like', '%' . strtoupper($request->location) . '%')
                            ->orWhere(DB::raw('UPPER(address)'), 'like', '%' . strtoupper($request->location) . '%');
                });
            }

            $data = new KosCollection($query->paginate($perPage, ['*'], 'page', $currentPage));

            return $this->sendResponse($data);
        } catch (Throwable $e) {
            $this->report($e);
            return $this->sendError(400, "Whoops something wrong with #index");
        }
    }

    public function showUser($id)
    {
        try {
            $kos = Kos::with(['kosImage', 'room', 'facility', 'address'])
                        ->where('id', dekrip($id))->first();
            if(!$kos)
                return $this->sendError(404, 'Kos not found');

            return $this->sendResponse(new KosDetailResource($kos));
        } catch (Throwable $e) {
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #show');
        }
    }

    public function dashboard()
    {
        try {
            $kos = Kos::select(DB::raw('count(kos.id) as count'), DB::raw('sum(rooms.total_room) as total_room'),
                                DB::raw('sum(rooms.available_room) as available_room'))
                        ->leftJoin(DB::raw('(
                            select kos_id, sum(total_room) total_room, sum(available_room) available_room
                                    from rooms
                                    group by kos_id
                                ) rooms'), 'rooms.kos_id', '=', 'kos.id')
                        ->where('user_id', auth()->user()->id)->get();
            $userCredit = UserCredit::where('user_id', auth()->user()->id)->first();

            $item = [
                'jumlah_kos' => $kos->pluck('count'),
                'jumlah_kamar' => $kos->pluck('total_room'),
                'jumlah_kamar_tersedia' => $kos->pluck('available_room'),
                'jumlah_credit' => $userCredit->credit,
            ];

            return $this->sendResponse($item);
        } catch (Throwable $e) {
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #dashboard');
        }
    }
}
