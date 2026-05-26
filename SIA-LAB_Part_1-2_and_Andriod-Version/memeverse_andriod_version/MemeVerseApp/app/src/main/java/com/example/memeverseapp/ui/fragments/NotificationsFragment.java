package com.example.memeverseapp.ui.fragments;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.fragment.app.Fragment;
import com.example.memeverseapp.R;

public class NotificationsFragment extends Fragment {

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.fragment_notifications, container, false);

        TextView tvNotifications = view.findViewById(R.id.tvNotifications);
        if (tvNotifications != null) {
            tvNotifications.setText("NOTIFICATIONS\n\n" +
                    "Coming Soon!\n\n" +
                    "You'll receive notifications for:\n" +
                    "• New likes on your memes\n" +
                    "• Comments on your posts\n" +
                    "• New followers\n" +
                    "• Private messages");
        }

        return view;
    }
}