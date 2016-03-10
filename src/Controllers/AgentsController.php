<?php

namespace Kordy\Ticketit\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Kordy\Ticketit\Models\Agent;
use Kordy\Ticketit\Models\Setting;

class AgentsController extends Controller
{
    public function index()
    {
        $agents = Agent::agents()->get();

        return view('ticketit::admin.agent.index', compact('agents'));
    }

    public function create()
    {
        $users = Agent::paginate(Setting::grab('paginate_items'));

        return view('ticketit::admin.agent.create', compact('users'));
    }

    public function store(Request $request)
    {
        $agents_list = $this->addAgents($request->input('agents'));
        $agents_names = implode(',', $agents_list);

        Session::flash('status', trans('ticketit::lang.agents-are-added-to-agents', ['names' => $agents_names]));

        return redirect()->action('\Kordy\Ticketit\Controllers\AgentsController@index');
    }

    public function update($id, Request $request)
    {
        $result = $this->syncAgentCategories($id, $request);

        if ($request->ajax()) {
            $response = response()->json(['categories' => Agent::find($id)->categories]);
            return $result ? $response : null;
        }
        else
        {
            Session::flash('status', trans('ticketit::lang.agents-joined-categories-ok'));
            return redirect()->action('\Kordy\Ticketit\Controllers\AgentsController@index');
        }
    }

    public function destroy($id)
    {
        $agent = $this->removeAgent($id);

        Session::flash('status', trans('ticketit::lang.agents-is-removed-from-team', ['name' => $agent->name]));

        return redirect()->action('\Kordy\Ticketit\Controllers\AgentsController@index');
    }

    /**
     * Assign users as agents.
     *
     * @param $user_ids
     *
     * @return array
     */
    public function addAgents($user_ids)
    {
        $users = Agent::find($user_ids);
        foreach ($users as $user) {
            $user->ticketit_agent = true;
            $user->save();
            $users_list[] = $user->name;
        }

        return $users_list;
    }

    /**
     * Remove user from the agents.
     *
     * @param $id
     *
     * @return mixed
     */
    public function removeAgent($id)
    {
        $agent = Agent::find($id);
        $agent->ticketit_agent = false;
        $agent->save();

        // Remove him from tickets categories as well

        $agent_cats = $agent->categories->lists('id')->toArray();
        $agent->categories()->detach($agent_cats);

        return $agent;
    }

    /**
     * Sync Agent categories with the selected categories got from update form.
     *
     * @param $id
     * @param Request $request
     */
    public function syncAgentCategories($id, Request $request)
    {
        $form_cat = ($request->input('category-id') == null) ? '' : $request->input('category-id');
        $form_action = ($request->input('_action') == null) ? "save" : $request->input('_action');
        $agent = Agent::find($id);
        $form_cats = [];
        if ($form_action == "delete") {
            $categories = $agent->categories;
            foreach ($categories as $category) {
                if ($form_cat != $category->id) {
                    $form_cats[] = $category->id;
                }
            }
            $agent->categories()->sync($form_cats);
        }
        else {
            $categories = $agent->categories;
            $form_cats[] = $form_cat;
            foreach ($categories as $category) {
                $form_cats[] = $category->id;
            }
            $agent->categories()->sync($form_cats);
        }
        return true;
    }
}
