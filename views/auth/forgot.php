<div class="w-full px-5">
        <h2 class="text-2xl font-bold text-[#002D74]">Login</h2>
        <p class="text-sm mt-4 text-[#002D74]">If you have an account, please login</p>
        <form class="mt-6" action="#" method="POST">
          <div>
            <label class="block text-gray-700">Email Address</label>
            <input type="email" name="" id="" placeholder="Enter Email Address" class="w-full px-4 py-3 rounded-lg bg-gray-200 mt-2 border focus:border-blue-500 focus:bg-white focus:outline-none" autofocus autocomplete required>
          </div>

          <div class="text-right mt-2">
            <a href="<?= atlas('login') ?>" class="text-sm font-semibold text-gray-700 hover:text-blue-700 focus:text-blue-700">I remember my password now</a>
          </div>

          <button type="submit" class="w-full block bg-blue-500 hover:bg-blue-400 focus:bg-blue-400 text-white font-semibold rounded-lg
                px-4 py-3 mt-6">Send Password Reset</button>
        </form>        
      </div>
    </div>

