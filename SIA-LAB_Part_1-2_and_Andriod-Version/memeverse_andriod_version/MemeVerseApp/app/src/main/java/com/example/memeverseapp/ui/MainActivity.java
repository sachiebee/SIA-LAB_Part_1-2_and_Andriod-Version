package com.example.memeverseapp.ui;

import android.content.Intent;
import android.os.Bundle;
import android.view.MenuItem;
import android.widget.Toast;
import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.app.AppCompatDelegate;
import androidx.appcompat.widget.Toolbar;
import androidx.core.view.GravityCompat;
import androidx.drawerlayout.widget.DrawerLayout;
import androidx.fragment.app.Fragment;
import com.google.android.material.bottomnavigation.BottomNavigationView;
import com.google.android.material.navigation.NavigationView;
import com.example.memeverseapp.R;
import com.example.memeverseapp.ui.fragments.HomeFragment;
import com.example.memeverseapp.ui.fragments.ProfileFragment;
import com.example.memeverseapp.utils.PreferencesManager;
import de.hdodenhof.circleimageview.CircleImageView;

public class MainActivity extends AppCompatActivity {
    private DrawerLayout drawerLayout;
    private BottomNavigationView bottomNav;
    private PreferencesManager prefManager;
    private CircleImageView navAvatar;
    private NavigationView navigationView;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        // Apply dark mode before super.onCreate
        prefManager = new PreferencesManager(this);
        if (prefManager.isDarkMode()) {
            AppCompatDelegate.setDefaultNightMode(AppCompatDelegate.MODE_NIGHT_YES);
        } else {
            AppCompatDelegate.setDefaultNightMode(AppCompatDelegate.MODE_NIGHT_NO);
        }

        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        // Check if user is logged in
        if (!prefManager.isLoggedIn()) {
            goToLogin();
            return;
        }

        // Initialize views
        Toolbar toolbar = findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);

        drawerLayout = findViewById(R.id.drawerLayout);
        bottomNav = findViewById(R.id.bottom_navigation);
        navAvatar = findViewById(R.id.navAvatar);
        navigationView = findViewById(R.id.navView);

        // Setup bottom navigation
        bottomNav.setOnItemSelectedListener(item -> {
            Fragment selectedFragment = null;
            int itemId = item.getItemId();

            if (itemId == R.id.nav_home) {
                selectedFragment = new HomeFragment();
            } else if (itemId == R.id.nav_profile) {
                selectedFragment = new ProfileFragment();
            }

            if (selectedFragment != null) {
                getSupportFragmentManager().beginTransaction()
                        .replace(R.id.fragment_container, selectedFragment)
                        .commit();
            }
            return true;
        });

        // Set default fragment
        bottomNav.setSelectedItemId(R.id.nav_home);

        // Setup drawer navigation
        navigationView.setNavigationItemSelectedListener(item -> {
            int itemId = item.getItemId();

            if (itemId == R.id.nav_home) {
                bottomNav.setSelectedItemId(R.id.nav_home);
            } else if (itemId == R.id.nav_profile) {
                bottomNav.setSelectedItemId(R.id.nav_profile);
            } else if (itemId == R.id.nav_upload) {
                Toast.makeText(this, "Upload feature coming soon", Toast.LENGTH_SHORT).show();
            } else if (itemId == R.id.nav_logout) {
                logout();
            }

            drawerLayout.closeDrawers();
            return true;
        });

        // Setup avatar click to open drawer
        navAvatar.setOnClickListener(v -> {
            drawerLayout.openDrawer(GravityCompat.START);
        });

        // Show welcome message
        String username = prefManager.getUsername();
        if (username != null && !username.isEmpty()) {
            Toast.makeText(this, "Welcome " + username + "!", Toast.LENGTH_SHORT).show();
        }
    }

    private void goToLogin() {
        Intent intent = new Intent(this, LoginActivity.class);
        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
        startActivity(intent);
        finish();
    }

    private void logout() {
        prefManager.clear();
        goToLogin();
    }

    @Override
    public boolean onOptionsItemSelected(@NonNull MenuItem item) {
        if (item.getItemId() == android.R.id.home) {
            drawerLayout.openDrawer(GravityCompat.START);
            return true;
        }
        return super.onOptionsItemSelected(item);
    }
}