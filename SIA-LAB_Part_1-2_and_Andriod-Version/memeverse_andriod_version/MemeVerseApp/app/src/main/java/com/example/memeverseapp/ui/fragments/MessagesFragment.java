package com.example.memeverseapp.ui.fragments;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.fragment.app.Fragment;
import com.example.memeverseapp.R;

public class MessagesFragment extends Fragment {

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.fragment_messages, container, false);

        TextView tvMessages = view.findViewById(R.id.tvMessages);
        if (tvMessages != null) {
            tvMessages.setText("MESSAGES\n\nComing Soon!\n\nThis feature will allow you to send private messages to other users.");
        }

        return view;
    }
}