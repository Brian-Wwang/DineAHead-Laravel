<x-layouts.app :title="__('Menu')">
  <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-white">Menus</h2>
        <button onclick="openCreate()" class="px-4 py-2 bg-indigo-600  rounded-md hover:bg-indigo-700">
          + New Menu
        </button>
      </div>

      <div class="overflow-auto rounded-lg shadow">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
          <thead class="bg-zinc-100 dark:bg-zinc-800">
            <tr>
              @foreach(['ID', 'Name', 'Desc', 'Price', 'Order', 'Active', 'Actions'] as $heading)
                <th class="px-6 py-3 text-left text-sm font-semibold tracking-wider text-zinc-700 dark:text-zinc-300">
                  {{ $heading }}
                </th>
              @endforeach
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
            @foreach ($menus as $menu)
              <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                <td class="px-6 py-4 whitespace-nowrap">{{ $menu->id }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ $menu->name }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ $menu->desc }}</td>
                <td class="px-6 py-4 whitespace-nowrap">${{ number_format($menu->price, 2) }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ $menu->order }}</td>
                <td class="px-6 py-4">
                  <span class="inline-flex px-2 py-1 text-xs rounded-full font-semibold
                               {{ $menu->active ? 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-white'
                                                : 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-white' }}">
                    {{ $menu->active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right whitespace-nowrap space-x-2">
                  <button onclick='openEdit(@json($menu))' class="text-indigo-600 hover:text-indigo-900 text-sm">
                    Edit
                  </button>
                  <form method="POST" action="{{ route('menus.destroy', $menu) }}" class="inline-block" onsubmit="return confirm('Delete this menu?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-900 text-sm">
                      Delete
                    </button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <div id="menuModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <form method="POST" id="menuForm" class="w-full max-w-lg bg-white dark:bg-zinc-800 rounded-lg p-6 space-y-4">
      @csrf
      <input type="hidden" id="formMethod" name="_method" value="POST">
      <h3 id="menuModalTitle" class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Create Menu</h3>

      <div>
        <label for="menuName" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Name</label>
        <input id="menuName" name="name" required class="w-full mt-1 p-2 border rounded dark:bg-zinc-700 dark:border-zinc-600 dark:text-white">
      </div>

      <div>
        <label for="menuDesc" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Description</label>
        <textarea id="menuDesc" name="desc" class="w-full mt-1 p-2 border rounded dark:bg-zinc-700 dark:border-zinc-600 dark:text-white"></textarea>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label for="menuPrice" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Price</label>
          <input id="menuPrice" name="price" type="number" step="0.01" required class="w-full mt-1 p-2 border rounded dark:bg-zinc-700 dark:border-zinc-600 dark:text-white">
        </div>
      </div>

      <div class="flex items-center gap-2">
        <input type="checkbox" id="menuActive" name="active" value="1" class="h-4 w-4 rounded border dark:bg-zinc-700 dark:border-zinc-600">
        <label for="menuActive" class="text-sm text-zinc-700 dark:text-zinc-300">Active</label>
      </div>

      <div class="flex justify-end space-x-2 pt-4">
        <button type="button" onclick="closeModal()" class="px-4 py-2 border rounded hover:bg-zinc-100 dark:hover:bg-zinc-700">
          Cancel
        </button>
        <button type="submit" class="px-4 py-2 bg-indigo-600 rounded hover:bg-indigo-700">
          Save
        </button>
      </div>
    </form>
  </div>

  <!-- JS 控制 Modal -->
  <script>
    function openCreate() {
      const modal = document.getElementById('menuModal');
      document.getElementById('menuForm').action = "{{ route('menus.store') }}";
      document.getElementById('formMethod').value = "POST";
      document.getElementById('menuModalTitle').innerText = "Create Menu";
      document.getElementById('menuForm').reset();
      document.getElementById('menuActive').checked = true;
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }

    function openEdit(menu) {
      const modal = document.getElementById('menuModal');
      document.getElementById('menuForm').action = `/menus/${menu.id}`;
      document.getElementById('formMethod').value = "PUT";
      document.getElementById('menuModalTitle').innerText = "Edit Menu";

      document.getElementById('menuName').value = menu.name;
      document.getElementById('menuDesc').value = menu.desc ?? '';
      document.getElementById('menuPrice').value = menu.price;
      document.getElementById('menuActive').checked = menu.active;

      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }

    function closeModal() {
      const modal = document.getElementById('menuModal');
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }
  </script>
</x-layouts.app>
