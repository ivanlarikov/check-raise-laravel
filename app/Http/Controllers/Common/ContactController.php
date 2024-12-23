<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\Common\ContactCreateRequest;
use App\Http\Requests\Common\ContactUpdateRequest;
use App\Traits\Response\ResponseTrait;
use App\Services\Common\ContactService;
use App\Http\Resources\Common\ContactResourceCollection;
use App\Http\Resources\Common\ContactResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
  use ResponseTrait;

  /**
   * @var ContactService
   */
  protected ContactService $contact;

  /**
   * @param ContactService $contact
   */

  public function __construct(ContactService $contact)
  {
    $this->contact = $contact;
  }

  /**
   * @param ContactCreateRequest $request
   * @return JsonResponse
   */
  public function store(ContactCreateRequest $request): JsonResponse
  {
    $data = $request->validated();
    $room = $data['room'];

    if($room === 'admin') {
      $notificationType = 'admin';
      $notificationSlug = 'contact_admin';
    } else {
      $notificationType = 'manager';
      $notificationSlug = 'contact';
    }

    sendEmail($notificationType, $notificationSlug, $room, $data);

    $contact = $this->contact->create(
      $data
    );
    if ($contact) {
      return $this->jsonResponseSuccess(
        trans('common/contact.create.success')
      );
    }

    return $this->jsonResponseFail(
      trans('common/contact.create.fail'),
      400
    );
  }

  /**
   * @param Request $request
   * @return ContactResourceCollection
   */
  public function index(Request $request): ContactResourceCollection
  {

    return ContactResourceCollection::make(
      $this->contact->all(null, null, null, null, null, null, 100, true)
    );
  }

  /**
   * Display the specified resource.
   *
   * @param int $id
   * @return JsonResponse | ContactResource
   */
  public function show(int $id): ContactResource|JsonResponse
  {
    $room = $this->contact->show(
      ['id' => $id]
    );

    if ($room) {
      return new ContactResource(
        $room
      );
    }
    return $this->jsonResponseFail(
      trans('common/contact.show.fail')
    );
  }

  public function update(ContactUpdateRequest $request): JsonResponse
  {
    $data = $request->validated();

    $contact = $this->contact->update($data['id'], $data);

    if ($contact)
      return $this->jsonResponseSuccess(
        trans('common/contact.update.success')
      );
    return $this->jsonResponseFail(
      trans('common/contact.update.fail')
    );
  }

  public function destroy($id): JsonResponse
  {

    $this->contact->delete($id);
    return $this->jsonResponseSuccess(
      trans('common/contact.delete.success')
    );
  }
}
