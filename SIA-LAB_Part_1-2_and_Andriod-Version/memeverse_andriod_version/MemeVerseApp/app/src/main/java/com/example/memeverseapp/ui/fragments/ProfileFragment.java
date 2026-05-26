package com.example.memeverseapp.ui.fragments;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.fragment.app.Fragment;
import com.example.memeverseapp.R;
import com.example.memeverseapp.utils.PreferencesManager;

public class ProfileFragment extends Fragment {
    private TextView tvProfileInfo;
    private PreferencesManager prefManager;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.fragment_profile, container, false);

        tvProfileInfo = view.findViewById(R.id.tvProfileInfo);
        prefManager = new PreferencesManager(requireContext());

        int userId = prefManager.getUserId();
        String username = prefManager.getUsername();
        String email = prefManager.getEmail();

        if (userId == 0) {
            tvProfileInfo.setText("PROFILE\n\nNot logged in!\nPlease login again.");
        } else {
            String profileText = "PROFILE\n\n" +
                    "User ID: " + userId + "\n" +
                    "Username: " + (username != null && !username.isEmpty() ? username : "N/A") + "\n" +
                    "Email: " + (email != null && !email.isEmpty() ? email : "N/A") + "\n\n" +
                    "Coming Soon:\n" +
                    "• Edit profile\n" +
                    "• Upload avatar\n" +
                    "• View followers/following\n" +
                    "• See your posts";
            tvProfileInfo.setText(profileText);
        }

        return view;
    }
}