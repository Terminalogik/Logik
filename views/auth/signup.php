<div class="md:w-1/2 px-5">
        <h2 class="text-2xl font-bold text-[#002D74]">Join The Horde</h2>
        <p class="text-sm mt-4 text-[#002D74]">If you have an account, click login below</p>
  <?php if (!empty($errors)) { echo '<div class="bg-red-100 text-red-700 p-3 mb-4 rounded">' . implode('<br>', $errors) . '</div>'; } ?>
  <?php if (!empty($success)) { echo '<div class="bg-green-100 text-green-700 p-3 mb-4 rounded">' . htmlspecialchars($success) . '</div>'; } ?>
  <?php if (empty($errors) && empty($success) && $_SERVER['REQUEST_METHOD'] === 'POST') {
      echo '<div class="bg-yellow-100 text-yellow-700 p-3 mb-4 rounded">Debug: No error or success message was set. Registration logic may not be running or is redirecting prematurely.</div>';
  } ?>
  <form class="mt-6" action="<?= atlas('signup') ?>" method="POST">
          <div>
            <label class="block text-gray-700">Email Address</label>
            <input type="email" name="txtInfectedEmail" id="txtInfectedEmail" placeholder="Enter Email Address" class="w-full px-4 py-3 rounded-lg bg-gray-200 mt-2 border focus:border-blue-500 focus:bg-white focus:outline-none" autofocus autocomplete required>
          </div>
          <div>
            <label class="block text-gray-700">Firstname</label>
            <input type="text" name="txtInfectedFirstname" id="txtInfectedFirstname" placeholder="Jane" class="w-full px-4 py-3 rounded-lg bg-gray-200 mt-2 border focus:border-blue-500 focus:bg-white focus:outline-none" autofocus autocomplete required>
          </div>
          <div>
            <label class="block text-gray-700">Lastname</label>
            <input type="text" name="txtInfectedLastname" id="txtInfectedLastname" placeholder="Smith" class="w-full px-4 py-3 rounded-lg bg-gray-200 mt-2 border focus:border-blue-500 focus:bg-white focus:outline-none" autofocus autocomplete required>
          </div>

          <div class="mt-4">
            <label class="block text-gray-700">Password</label>
            <input type="password" name="txtInfectedPassword" id="txtInfectedPassword" placeholder="Enter Password" minlength="6" class="w-full px-4 py-3 rounded-lg bg-gray-200 mt-2 border focus:border-blue-500
                  focus:bg-white focus:outline-none" required>
          </div>

          <div class="mt-4">
            <label class="block text-gray-700">Confirm Password</label>
            <input type="password" name="txtInfectedConfirmPassword" id="txtInfectedConfirmPassword" placeholder="Confirm Password" minlength="6" class="w-full px-4 py-3 rounded-lg bg-gray-200 mt-2 border focus:border-blue-500
                  focus:bg-white focus:outline-none" required>
          </div>

          <div class="text-right mt-2">
            <a href="<?= atlas('forgot') ?>" class="text-sm font-semibold text-gray-700 hover:text-blue-700 focus:text-blue-700">Forgot Password?</a>
          </div>

          <button type="submit" class="w-full block bg-blue-500 hover:bg-blue-400 focus:bg-blue-400 text-white font-semibold rounded-lg px-4 py-3 mt-6 text-center">Join the Horde</button>
        </form>

        <div class="mt-7 grid grid-cols-3 items-center text-gray-500">
          <hr class="border-gray-500" />
          <p class="text-center text-sm">OR</p>
          <hr class="border-gray-500" />
        </div>

        <button class="bg-white border py-2 w-full rounded-xl mt-5 flex justify-center items-center text-sm hover:scale-105 duration-300 ">
          <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" class="w-6 h-6" viewBox="0 0 48 48"><defs><path id="a" d="M44.5 20H24v8.5h11.8C34.7 33.9 30.1 37 24 37c-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 4.1 29.6 2 24 2 11.8 2 2 11.8 2 24s9.8 22 22 22c11 0 21-8 21-22 0-1.3-.2-2.7-.5-4z"/></defs><clipPath id="b"><use xlink:href="#a" overflow="visible"/></clipPath><path clip-path="url(#b)" fill="#FBBC05" d="M0 37V11l17 13z"/><path clip-path="url(#b)" fill="#EA4335" d="M0 11l17 13 7-6.1L48 14V0H0z"/><path clip-path="url(#b)" fill="#34A853" d="M0 37l30-23 7.9 1L48 0v48H0z"/><path clip-path="url(#b)" fill="#4285F4" d="M48 48L17 24l-4-3 35-10z"/></svg>
          <span class = "ml-4">Login with Google</span>
        </button>

        <div class="text-sm flex justify-between items-center mt-3">
          <p>I'm already a zombie...</p>
          <a href="<?= atlas('login') ?>" class="w-250 block bg-gray-400 hover:bg-gray-200 focus:bg-gray-400 text-white font-semibold rounded-sm px-4 py-3 mt-6 text-center">Login</a>
        </div>
      </div>

      <div class="w-1/2 md:block hidden">
        <img src="siteimgs/piczombie.png" class="rounded-2xl" alt="page img" style="height: 300px; margin-top: 150px;">
      </div>

    </div>
