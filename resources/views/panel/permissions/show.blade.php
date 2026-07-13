<div class="card-table">
    <div class="card">
        <div class="card-header card-header-divider pb-0" style="border-bottom: 0"><b>List Permissions</b></div>
        <div class="card-body" style="padding: 8px 20px 20px">
            <table class="table table-sm table-hover table-bordered table-striped">
                <thead>
                <tr>
                    <th style="text-align: center;">Module</th>
                    <th style="text-align: center;">Add</th>
                    <th style="text-align: center;">Edit</th>
                    <th style="text-align: center;">Delete</th>
                    <th style="text-align: center;">View</th>
                    <th style="text-align: center;">Module Visibility</th>
                    <th style="text-align: center;">Bypass Visibility</th>
                </tr>
                </thead>
                <tbody>
                @foreach($modules as $module)
                    @if($module['slug'] != 'dashboard')
                        @if(empty($module['children']))
                            <tr>
                                <td style="text-align: center;">{{ $module['title'] }}</td>
                                <td style="text-align: center;">
                                    <label class="custom-control custom-checkbox custom-control-inline">
                                        <input type="hidden" name="add[{{ $module['slug'] }}]" value="0">
                                        <input type="checkbox" class="custom-control-input"
                                               name="add[{{ $module['slug'] }}]" value="1"><span
                                            class="custom-control-label custom-control-color"></span>
                                    </label>
                                </td>
                                <td style="text-align: center;">
                                    <label class="custom-control custom-checkbox custom-control-inline">
                                        <input type="hidden" name="edit[{{ $module['slug'] }}]" value="0">
                                        <input type="checkbox" class="custom-control-input"
                                               name="edit[{{ $module['slug'] }}]" value="1"><span
                                            class="custom-control-label custom-control-color"></span>
                                    </label>
                                </td>
                                <td style="text-align: center;">
                                    <label class="custom-control custom-checkbox custom-control-inline">
                                        <input type="hidden" name="delete[{{ $module['slug'] }}]" value="0">
                                        <input type="checkbox" class="custom-control-input"
                                               name="delete[{{ $module['slug'] }}]" value="1"><span
                                            class="custom-control-label custom-control-color"></span>
                                    </label>
                                </td>
                                <td style="text-align: center;">
                                    <label class="custom-control custom-checkbox custom-control-inline">
                                        <input type="hidden" name="view[{{ $module['slug'] }}]" value="0">
                                        <input type="checkbox" class="custom-control-input"
                                               name="view[{{ $module['slug'] }}]" value="1"><span
                                            class="custom-control-label custom-control-color"></span>
                                    </label>
                                </td>
                                <td style="text-align: center;">
                                    <label class="custom-control custom-checkbox custom-control-inline">
                                        <input type="hidden" name="is_visible[{{ $module['slug'] }}]" value="0">
                                        <input type="checkbox" class="custom-control-input"
                                               name="is_visible[{{ $module['slug'] }}]" value="1"><span
                                            class="custom-control-label custom-control-color"></span>
                                    </label>
                                </td>
                                <td style="text-align: center;">
                                    <label class="custom-control custom-checkbox custom-control-inline">
                                        <input type="hidden" name="bypass_visibility[{{ $module['slug'] }}]" value="0">
                                        <input type="checkbox" class="custom-control-input"
                                               name="bypass_visibility[{{ $module['slug'] }}]" value="1"><span
                                            class="custom-control-label custom-control-color"></span>
                                    </label>
                                </td>
                            </tr>
                        @else
                            @foreach($module['children'] as $children)
                                <tr>
                                    <td style="text-align: center;">{{ $children['title'] }}</td>
                                    <td style="text-align: center;">
                                        <label class="custom-control custom-checkbox custom-control-inline">
                                            <input type="hidden" name="add[{{ $children['slug'] }}]" value="0">
                                            <input type="checkbox" class="custom-control-input"
                                                   name="add[{{ $children['slug'] }}]" value="1"><span
                                                class="custom-control-label custom-control-color"></span>
                                        </label>
                                    </td>
                                    <td style="text-align: center;">
                                        <label class="custom-control custom-checkbox custom-control-inline">
                                            <input type="hidden" name="edit[{{ $children['slug'] }}]" value="0">
                                            <input type="checkbox" class="custom-control-input"
                                                   name="edit[{{ $children['slug'] }}]" value="1"><span
                                                class="custom-control-label custom-control-color"></span>
                                        </label>
                                    </td>
                                    <td style="text-align: center;">
                                        <label class="custom-control custom-checkbox custom-control-inline">
                                            <input type="hidden" name="delete[{{ $children['slug'] }}]" value="0">
                                            <input type="checkbox" class="custom-control-input"
                                                   name="delete[{{ $children['slug'] }}]"
                                                   value="1"><span
                                                class="custom-control-label custom-control-color"></span>
                                        </label>
                                    </td>
                                    <td style="text-align: center;">
                                        <label class="custom-control custom-checkbox custom-control-inline">
                                            <input type="hidden" name="view[{{ $children['slug'] }}]" value="0">
                                            <input type="checkbox" class="custom-control-input"
                                                   name="view[{{ $children['slug'] }}]" value="1"><span
                                                class="custom-control-label custom-control-color"></span>
                                        </label>
                                    </td>
                                    <td style="text-align: center;">
                                        <label class="custom-control custom-checkbox custom-control-inline">
                                            <input type="hidden" name="is_visible[{{ $children['slug'] }}]" value="0">
                                            <input type="checkbox" class="custom-control-input"
                                                   name="is_visible[{{ $children['slug'] }}]" value="1"><span
                                                class="custom-control-label custom-control-color"></span>
                                        </label>
                                    </td>
                                    <td style="text-align: center;">
                                        <label class="custom-control custom-checkbox custom-control-inline">
                                            <input type="hidden" name="bypass_visibility[{{ $module['slug'] }}]" value="0">
                                            <input type="checkbox" class="custom-control-input"
                                                   name="bypass_visibility[{{ $module['slug'] }}]" value="1"><span
                                                class="custom-control-label custom-control-color"></span>
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
